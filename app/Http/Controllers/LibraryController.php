<?php

namespace App\Http\Controllers;

use App\Models\Library;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LibraryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $libraries = Library::withCount('versions')
            ->orderBy('system')
            ->orderBy('name')
            ->get();

        return Inertia::render('Libraries/Index', [
            'libraries' => $libraries,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'system' => 'required|string|max:100',
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = Library::where('system', $request->input('system'))
                        ->where('name', $value)
                        ->exists();
                    if ($exists) {
                        $fail('Já existe uma biblioteca com este nome neste sistema.');
                    }
                },
            ],
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        Library::create($validated);

        return redirect()->route('libraries.index')
            ->with('success', 'Biblioteca criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Library $library)
    {
        $library->load(['versions' => function ($query) {
            $query->orderByRaw('client_cnpj IS NULL DESC')
                ->orderBy('client_cnpj')
                ->orderBy('created_at', 'desc');
        }]);

        return Inertia::render('Libraries/Show', [
            'library' => $library,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Library $library)
    {
        $validated = $request->validate([
            'system' => 'required|string|max:100',
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $library) {
                    $exists = Library::where('system', $request->input('system'))
                        ->where('name', $value)
                        ->where('id', '!=', $library->id)
                        ->exists();
                    if ($exists) {
                        $fail('Já existe uma biblioteca com este nome neste sistema.');
                    }
                },
            ],
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $library->update($validated);

        return redirect()->route('libraries.index')
            ->with('success', 'Biblioteca atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Library $library)
    {
        if ($library->versions()->count() > 0) {
            return back()->with('error', 'Não é possível excluir biblioteca com versões cadastradas!');
        }

        $library->delete();

        return redirect()->route('libraries.index')
            ->with('success', 'Biblioteca excluída com sucesso!');
    }
}
