<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportUsersRequest;
use App\Services\Admin\UserCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    public function usersForm(): View
    {
        return view('admin.import.users');
    }

    public function usersStore(ImportUsersRequest $request, UserCsvImportService $importer): RedirectResponse
    {
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        if ($path === false) {
            return redirect()
                ->route('admin.import.users.form')
                ->with('error', 'Không đọc được file đã tải lên.');
        }

        $result = $importer->importFromPath($path);

        return redirect()
            ->route('admin.import.users.form')
            ->with('import_result', $result);
    }

    public function usersTemplateCsv(): StreamedResponse
    {
        $filename = 'mau_import_users.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['name', 'email', 'password', 'can_post', 'can_comment']);
            fputcsv($out, ['Vi Du', 'vidu@example.com', 'MatKhauToiThieu8KyTu', '1', '1']);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
