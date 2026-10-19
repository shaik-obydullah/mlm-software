<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(): View
    {
        $tickets = Ticket::where('user_id', auth()->id())
            ->withCount('replies')->orderByDesc('id')->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:general,withdrawal,commission,technical,other'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string'],
        ]);

        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        return redirect()->route('web.tickets.show', $ticket)->with('success', 'Ticket created.');
    }

    public function show(Ticket $ticket): View|RedirectResponse
    {
        if (! auth()->user()->isAdmin() && $ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $ticket->load('replies.user:id,name,unique_id,role');

        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        if (! auth()->user()->isAdmin() && $ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        $ticket->update(['status' => auth()->user()->isAdmin() ? 'pending' : 'open']);

        return back()->with('success', 'Reply added.');
    }
}
