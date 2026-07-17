<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\Category;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();
        
        // Ambil data tugas yang aktif (belum dihapus)
        $todos = Todo::with('category')
            ->when($request->filled('search'), function($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('category_id'), function($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            })
            ->orderBy('due_date', 'asc')
            ->get();

        // Ambil data tugas yang ada di Kotak Sampah (Soft Deleted)
        $trashedTodos = Todo::onlyTrashed()->with('category')->get();

        return view('todos', compact('todos', 'categories', 'trashedTodos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'required|in:low,medium,high',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        Todo::create($request->all());

        return redirect()->route('todos.index')->with('success', 'Tugas baru berhasil ditambahkan!');
    }

    public function updateStatus(Request $request, Todo $todo)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $todo->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status tugas berhasil diperbarui!');
    }

    // Fungsi Hapus Sementara (Pindah ke Sampah)
    public function destroy(Todo $todo)
    {
        $todo->delete();
        return redirect()->back()->with('success', 'Tugas berhasil dipindahkan ke Kotak Sampah!');
    }

    // Fungsi Memulihkan Tugas
    public function restore($id)
    {
        $todo = Todo::withTrashed()->findOrFail($id);
        $todo->restore();
        return redirect()->back()->with('success', 'Tugas berhasil dipulihkan!');
    }

    // Fungsi Hapus Permanen
    public function forceDelete($id)
    {
        $todo = Todo::withTrashed()->findOrFail($id);
        $todo->forceDelete();
        return redirect()->back()->with('success', 'Tugas telah dihapus secara permanen!');
    }
}