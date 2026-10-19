<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::with('user:id,name,unique_id')->withCount('replies')->orderByDesc('id');

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:general,withdrawal,commission,technical,other'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string'],
        ]);

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return response()->json([
            'message' => 'Ticket created.',
            'ticket' => $ticket->load('replies'),
        ], 201);
    }

    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize($request, $ticket);

        return response()->json([
            'ticket' => $ticket->load('user:id,name,unique_id', 'replies.user:id,name,unique_id,role'),
        ]);
    }

    public function reply(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize($request, $ticket);

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        $ticket->update([
            'status' => $request->user()->isAdmin() ? 'pending' : 'open',
        ]);

        return response()->json([
            'message' => 'Reply added.',
            'reply' => $reply->load('user:id,name,unique_id'),
        ], 201);
    }

    public function status(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,pending,resolved,closed'],
        ]);

        $ticket->update([
            'status' => $validated['status'],
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed']) ? now() : null,
        ]);

        return response()->json(['message' => 'Ticket status updated.', 'ticket' => $ticket]);
    }

    private function authorize(Request $request, Ticket $ticket): void
    {
        if (! $request->user()->isAdmin() && $request->user()->id !== $ticket->user_id) {
            abort(403, 'You can only access your own tickets.');
        }
    }
}
