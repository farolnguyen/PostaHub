"""
PostaHub local embedding API — intfloat/multilingual-e5-small (384-dim).
E5 asymmetric prefixes: query vs passage (see intfloat README).
"""

from __future__ import annotations

import os
from contextlib import asynccontextmanager
from typing import Literal

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field
from sentence_transformers import SentenceTransformer

MODEL_NAME = os.environ.get(
    "EMBEDDING_MODEL", "intfloat/multilingual-e5-small"
)
MAX_BATCH = max(1, int(os.environ.get("EMBEDDING_MAX_BATCH", "32")))

_model: SentenceTransformer | None = None


def _prefix_for(input_type: Literal["query", "document"], text: str) -> str:
    t = text.strip()
    if not t:
        return t
    p = "query: " if input_type == "query" else "passage: "
    if t.startswith("query: ") or t.startswith("passage: "):
        return t
    return p + t


@asynccontextmanager
async def lifespan(app: FastAPI):
    global _model
    _model = SentenceTransformer(MODEL_NAME)
    yield
    _model = None


app = FastAPI(title="PostaHub Embedding", lifespan=lifespan)


@app.get("/")
def root():
    """Trang gốc — tránh 404 khi mở http://127.0.0.1:8001/ trên trình duyệt."""
    return {
        "service": "PostaHub Embedding",
        "model": MODEL_NAME,
        "endpoints": {
            "health": "/health",
            "embed": "POST /v1/embed",
            "openapi": "/openapi.json",
            "interactive_docs": "/docs",
        },
    }


class EmbedRequest(BaseModel):
    inputs: list[str] = Field(..., min_length=1)
    input_type: Literal["query", "document"] = "document"


class EmbedResponse(BaseModel):
    model: str
    dimensions: int
    vectors: list[list[float]]


@app.get("/health")
def health():
    if _model is None:
        return {"status": "loading", "model": MODEL_NAME, "dimensions": None}
    v = _model.get_sentence_embedding_dimension()
    return {"status": "ok", "model": MODEL_NAME, "dimensions": v}


@app.post("/v1/embed", response_model=EmbedResponse)
def embed(body: EmbedRequest):
    if _model is None:
        raise HTTPException(status_code=503, detail="Model not loaded")

    if len(body.inputs) > MAX_BATCH:
        raise HTTPException(
            status_code=400,
            detail=f"At most {MAX_BATCH} inputs per request",
        )

    prefixed = [_prefix_for(body.input_type, s) for s in body.inputs]
    if any(not s for s in prefixed):
        raise HTTPException(
            status_code=400,
            detail="Empty strings are not allowed after trim",
        )

    vectors = _model.encode(
        prefixed,
        normalize_embeddings=True,
        show_progress_bar=False,
    )
    dim = int(vectors.shape[1])
    rows = vectors.tolist()
    return EmbedResponse(
        model=MODEL_NAME,
        dimensions=dim,
        vectors=rows,
    )
