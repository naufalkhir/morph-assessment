<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Models\Entry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    /**
     * GET /api/entries
     * Returns paginated, filterable, sortable list of entries.
     * Accepts query params: sort, order, per_page, search, date_from, date_to, currency
     */
    public function index(Request $request): JsonResponse
    {
        // Whitelist allowed sort columns — prevents SQL injection via column name
        $allowed = ['description', 'amount', 'currency', 'transaction_date', 'created_at'];
        $sort = in_array($request->sort, $allowed) ? $request->sort : 'created_at';

        // Only allow 'asc' or 'desc' — defaults to 'desc'
        $order = strtolower($request->order ?? 'desc') === 'asc' ? 'asc' : 'desc';

        // Clamp per_page between 1 and 100 — prevents abuse
        $perPage = (int) ($request->per_page ?? 10);
        $perPage = min(max($perPage, 1), 100);

        $entries = Entry::query()
            // when() only applies filter if the parameter exists
            ->when($request->search, fn($q, $s) => $q->where('description', 'like', "{$s}%"))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('transaction_date', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('transaction_date', '<=', $d))
            ->when($request->currency, fn($q, $c) => $q->where('currency', strtoupper($c)))
            ->orderBy($sort, $order)
            ->paginate($perPage); // returns data + pagination metadata

        return response()->json($entries);
    }

    /**
     * POST /api/entries
     * Validates via StoreEntryRequest before reaching this method.
     * Creates entry and returns 201 Created.
     */
    public function store(StoreEntryRequest $request): JsonResponse
    {
        // $request->validated() only returns fields that passed validation
        $entry = Entry::create($request->validated());

        return response()->json([
            'message' => 'Entry created successfully.',
            'data' => $entry,
        ], 201); // 201 = Created
    }

    /**
     * GET /api/entries/{id}
     * Laravel auto-resolves Entry model via Route Model Binding
     */
    public function show(Entry $entry): JsonResponse
    {
        return response()->json(['data' => $entry]);
    }

    /**
     * PUT /api/entries/{id}
     * Validates via UpdateEntryRequest (uses 'sometimes' — partial updates allowed)
     * Returns fresh data from DB after update
     */
    public function update(UpdateEntryRequest $request, Entry $entry): JsonResponse
    {
        $entry->update($request->validated());

        // fresh() re-fetches from DB to ensure response has latest data
        return response()->json([
            'message' => 'Entry updated successfully.',
            'data' => $entry->fresh(),
        ]);
    }

    /**
     * DELETE /api/entries/{id}
     * Returns 204 No Content — REST standard for successful delete
     */
    public function destroy(Entry $entry): JsonResponse
    {
        $entry->delete();

        return response()->json(null, 204); // 204 = No Content
    }
}
