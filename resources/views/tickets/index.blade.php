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
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden h-fit">
                <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-5 py-4 text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold leading-tight">Open a ticket</h3>
                            <p class="text-xs text-brand-100">We usually reply within 24 hours</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('web.tickets.store') }}" class="p-5 space-y-4">
                    @csrf
                    <div>
                        <label for="subject" class="block text-sm font-medium text-slate-700">Subject</label>
                        <div class="relative mt-1.5">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="w-4.5 h-4.5" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <input id="subject" type="text" name="subject" value="{{ old('subject') }}" required placeholder="What do you need help with?"
                                   class="w-full rounded-xl border-slate-300 bg-slate-50/50 py-3 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="category" class="block text-sm font-medium text-slate-700">Category</label>
                            <select id="category" name="category"
                                    class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 py-3 pr-4 pl-3 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                                <option value="general">General</option>
                                <option value="withdrawal">Withdrawal</option>
                                <option value="commission">Commission</option>
                                <option value="technical">Technical</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label for="priority" class="block text-sm font-medium text-slate-700">Priority</label>
                            <select id="priority" name="priority"
                                    class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 py-3 pr-4 pl-3 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-slate-700">Message</label>
                        <textarea id="message" name="message" rows="4" required placeholder="Describe your issue in detail..."
                                  class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 py-3 px-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-sm font-semibold py-3 shadow-md shadow-brand-600/25 transition-all hover:shadow-lg hover:shadow-brand-600/30 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
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
                                    <div class="flex items-center gap-2 ml-3">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                                            {{ $ticket->priority === 'urgent' ? 'bg-rose-100 text-rose-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($ticket->priority === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600')) }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                                            {{ $ticket->status === 'open' ? 'bg-emerald-100 text-emerald-700' : ($ticket->status === 'resolved' ? 'bg-sky-100 text-sky-700' : ($ticket->status === 'closed' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700')) }}">
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
