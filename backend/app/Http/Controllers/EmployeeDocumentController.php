<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentController extends Controller
{
    public function index($userId)
    {
        $documents = EmployeeDocument::where('user_id', $userId)->get();
        return response()->json($documents);
    }

    public function store(Request $request, $userId)
    {
        $request->validate([
            'type' => 'required|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('employee_documents', $fileName, 'public');

        $document = EmployeeDocument::create([
            'user_id' => $userId,
            'type' => $request->type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
        ]);

        return response()->json(['message' => 'Document uploaded', 'data' => $document], 201);
    }

    public function destroy($id)
    {
        $document = EmployeeDocument::findOrFail($id);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['message' => 'Document deleted']);
    }
}
