<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Tag;
use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Report::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'preview' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'url' => 'required|file|mimes:pdf|max:30720', // Allow PDFs up to 30MB
            'tags' => 'required|array',
        ], [
            'preview.image' => 'The preview must be an image.',
            'preview.mimes' => 'The preview must be a file of type: jpeg, png, jpg, gif, svg.',
            'preview.max' => 'The preview may not be greater than 4 MB.',
            'url.file' => 'The report must be a file.',
            'url.mimes' => 'The report must be a file of type: pdf.',
            'url.max' => 'The report may not be greater than 30 MB.',
        ]);

        // Store preview image if provided
        $previewPath = null;
        if ($request->hasFile('preview')) {
            $previewPhoto = $request->file('preview');
            $previewName = time() . '_' . $previewPhoto->getClientOriginalName();
            $previewPath = $previewPhoto->storeAs('uploads/previews', $previewName, 'public');
        }

        // Store PDF report file
        $reportFile = $request->file('url');
        $reportName = time() . '_' . $reportFile->getClientOriginalName();
        $urlPath = $reportFile->storeAs('uploads/reports', $reportName, 'public');

        // Loop through tags and create them if they don't exist


        $tags = [];
        foreach ($validated['tags'] as $tag) {
            $tag = strtolower($tag);
            //trim
            $tag = trim($tag);
            $tags[] = Tag::firstOrCreate(['name' => $tag])->id;
        }


        // Save record in the database
        $report = Report::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'preview' => $previewPath, // Stores only file path
            'url' => $urlPath, // Stores only file path

        ]);

        // Attach tags to the report
        $report->tags()->sync($tags);

        return response()->json($report->load([
            'tags'
        ]), 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        return $report->load(['tags']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        Gate::authorize('delete' , $report);
        $report->delete();

        return response()->json([
            'message' => 'Report deleted successfully.'
        ]);
    }
}
