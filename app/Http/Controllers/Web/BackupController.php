<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index()
    {
        $disk = $this->backupDisk();
        $files = collect(Storage::disk($disk)->allFiles($this->backupDirectory()))
            ->filter(fn (string $path) => str_ends_with(strtolower($path), '.zip'))
            ->map(fn (string $path) => [
                'path' => $path,
                'filename' => basename($path),
                'size' => Storage::disk($disk)->size($path),
                'created_at' => Storage::disk($disk)->lastModified($path),
            ])
            ->sortByDesc('created_at')
            ->values();

        return view('admin.backups.index', compact('files'));
    }

    public function create(): RedirectResponse
    {
        $exitCode = Artisan::call('backup:run');
        if ($exitCode !== 0) {
            return back()->withErrors([
                'backup' => 'No se pudo completar el respaldo: ' . trim(Artisan::output()),
            ]);
        }

        return back()->with('success', 'El respaldo se generó correctamente.');
    }

    public function download(string $filename): StreamedResponse
    {
        $path = $this->findBackupPath($filename);

        return Storage::disk($this->backupDisk())->download($path, $filename);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $path = $this->findBackupPath($filename);
        Storage::disk($this->backupDisk())->delete($path);

        return back()->with('success', 'El respaldo fue eliminado.');
    }

    private function findBackupPath(string $filename): string
    {
        abort_unless(
            preg_match('/\A[A-Za-z0-9._-]+\.zip\z/i', $filename) === 1,
            404
        );

        $matches = collect(Storage::disk($this->backupDisk())->allFiles($this->backupDirectory()))
            ->filter(fn (string $path) => basename($path) === $filename);

        abort_unless($matches->count() === 1, 404);

        return $matches->first();
    }

    private function backupDisk(): string
    {
        return config('backup.backup.destination.disks.0', 'local');
    }

    private function backupDirectory(): string
    {
        return config('backup.backup.name', config('app.name', 'Laravel'));
    }
}
