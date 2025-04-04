<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Tag;
use Illuminate\Support\Facades\Gate; // Add this at the top

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     * Search for reports by title or description.
     * And u can also search by tags
     */
    public function search(Request $request){
        $validated = $request->validate([
            'query' => 'nullable|string',
            'currentPage' => 'nullable|integer|min:1',
            'tags' => 'nullable|array', // Validate tags as an array if provided
            'tags.*' => 'nullable|string', // Each tag should be a string
        ]);

        $queryString = $validated['query'] ?? ''; // Default to empty string if no query provided
        $currentPage = $validated['currentPage'] ?? 1; // Default to page 1 if not provided
        $tags = $validated['tags'] ?? []; // Default to empty array if no tags provided

        $reports = null; // Initialize the result variable for Reports
        $hasMoreReports = false; // Initialize flag for more Reports

        // If tags are provided, fetch blogs that match the tags and only the validated ones
        if (!empty($tags)) {
            $reportsQuery = Report::with(relations: ['user', 'tags']) // Add relationships to load including 'tags'
                ->whereHas('tags', callback: function ($query) use ($tags) {
                    $query->whereIn(column: 'name', values: $tags); // Filter by tags
                })->where('validated', value: true); // Filter by validated reports

            // Fetch blogs and check if there are more
            $reports = $reportsQuery->take(11)->get(); // Fetch 11 blogs to check for "hasMoreBlogs"
            $hasMoreReports = $reports->count() > 10; // If more than 10 blogs are fetched, there are more blogs
            $reports = $reports->take(10); // Limit the result to 10 blogs
        } else {
            // Get blogs that match the search query
            $reportsQuery = Report::with(['user', 'tags']); // Add relationships to load

            if ($queryString) {
                $reportsQuery->where(function($query) use ($queryString) {
                    $query->where('title', 'like', '%' . $queryString . '%')
                          ->orWhere('description', 'like', '%' . $queryString . '%');
                })->where('validated', true);            
            }

            $reports = $reportsQuery->paginate(10, ['*'], 'page', $currentPage); // Paginated query
            $hasMoreReports = $reports->hasMorePages(); // Check if there are more pages
        }
        // Return the results
        return response()->json([
            'currentPage' => !empty($tags) ? 1 : $reports->currentPage(), // If tags are provided, currentPage will always be 1
            'hasMoreReports' => $hasMoreReports, // Indicate if there are more blogs
            'reports' => !empty($tags) ? $reports->values() : $reports->items(), // Use items() for paginated results, directly return $blogs for collections
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', arguments: Report::class);
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
        $previewPhoto = $request->file('preview');
        $previewName = time() . '_' . $previewPhoto->getClientOriginalName();
        $previewPath = $previewPhoto->storeAs('uploads/previews', $previewName, 'public');


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
            'validated' => false,
            'user_id' => auth()->id(),
        ]);

        // Attach tags to the report
        $report->tags()->sync($tags);

        return response()->json($report->load([
            'tags' , 'user'
        ]), 201);

    }

    /**
     * Download the specified report file.
     */

    public function download(Report $report){
        return Storage::disk('public')->download($report->url);
    }

    /**
     * Validate the specified report.
     */
    public function validateReport(Report $report){
        Gate::authorize('validate', $report);
        $report->update(['validated' => true]);
        return response()->json($report);
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        return $report->load(relations: ['tags' , 'user']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        Gate::authorize('update', $report);
        // Validate request data
        $validated = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'preview' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'url' => 'nullable|file|mimes:pdf|max:30720', // PDF max 30MB
            'tags' => 'nullable|array',
        ]);

        // Handle preview image update
        if ($request->hasFile('preview')) {
            // Delete old preview if exists
            if ($report->preview) {
                Storage::disk('public')->delete($report->preview);
            }
            // Store new preview
            $previewPhoto = $request->file('preview');
            $previewName = time() . '_' . $previewPhoto->getClientOriginalName();
            $validated['preview'] = $previewPhoto->storeAs('uploads/previews', $previewName, 'public');
        }

        // Handle PDF report file update
        if ($request->hasFile('url')) {
            // Delete old PDF file if exists
            if ($report->url) {
                Storage::disk('public')->delete($report->url);
            }
            // Store new report file
            $reportFile = $request->file('url');
            $reportName = time() . '_' . $reportFile->getClientOriginalName();
            $validated['url'] = $reportFile->storeAs('uploads/reports', $reportName, 'public');
        }

        // Update report details
        $report->update($validated);

        // Handle tags update (if provided)
        if ($request->has('tags')) {
            $tags = [];
            foreach ($validated['tags'] as $tag) {
                $tag = strtolower(trim($tag));
                $tags[] = Tag::firstOrCreate(['name' => $tag])->id;
            }
            $report->tags()->sync($tags);
        }

        return response()->json($report->load(['tags' , 'user']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        Gate::authorize('delete' , $report);

        // Delete the preview image
        $preview = $report->preview;
        if ($preview) {
            Storage::disk('public')->delete($preview);
        }
        // Delete the report file
        $url = $report->url;
        if ($url) {
            Storage::disk('public')->delete($url);
        }
        $report->delete();

        return response()->json([
            'message' => 'Report deleted successfully.'
        ]);
    }
}
