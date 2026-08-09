@extends('layouts.app')

@section('title', 'Support')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Support Tickets</h2>
                <p class="text-sm text-slate-500">Get help with your account, commissions and withdrawals.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5 h-fit">
                <h3 class="text-sm font-semibold text-slate-900 mb-4">Open a ticket</h3>
                <form method="POST" action="{{ route('web.tickets.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Subject</label>
                        <input type="text" name="subject" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Category</label>
                            <select name="category" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                <option value="general">General</option>
                                <option value="withdrawal">Withdrawal</option>
                                <option value="commission">Commission</option>
                                <option value="technical">Technical</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Priority</label>
                            <select name="priority" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Message</label>
                        <textarea name="message" rows="4" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-2.5 transition-colors">
                        Submit ticket
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Your tickets</h3>
                    @if($tickets->isEmpty())
                        <p class="text-sm text-slate-500">No tickets yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($tickets as $ticket)
                                <a href="{{ route('web.tickets.show', $ticket) }}"
                                   class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3 hover:border-brand-300 hover:bg-brand-50/50 transition-colors">
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-800 truncate">{{ $ticket->subject }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            {{ ucfirst($ticket->category) }} · {{ $ticket->replies_count }} reply(s) · {{ $ticket->created_at->format('d M Y') }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 ml-3">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                                            {{ $ticket->priority === 'urgent' ? 'bg-rose-50 text-rose-700' : ($ticket->priority === 'high' ? 'bg-orange-50 text-orange-700' : 'bg-slate-100 text-slate-600') }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                                            {{ $ticket->status === 'open' ? 'bg-emerald-50 text-emerald-700' : ($ticket->status === 'resolved' ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-600') }}">
                                            {{ ucfirst($ticket->status) }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        {{ $tickets->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
