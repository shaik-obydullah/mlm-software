@extends('layouts.app')

@section('title', 'Ticket #'.$ticket->id)

@section('content')
    <div class="max-w-3xl space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <a href="{{ route('web.tickets') }}" class="text-xs text-brand-600 hover:text-brand-700">&larr; Back to tickets</a>
                <h2 class="text-lg font-semibold text-slate-900 mt-1">{{ $ticket->subject }}</h2>
                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 capitalize">{{ $ticket->category }}</span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 capitalize">{{ $ticket->priority }}</span>
                    <span class="px-2 py-0.5 rounded-full
                        {{ $ticket->status === 'open' ? 'bg-emerald-50 text-emerald-700' : ($ticket->status === 'resolved' ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-600') }}">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </div>
            </div>
            @if(auth()->user()->isAdmin())
                <form method="POST" action="{{ route('web.tickets.reply', $ticket) }}">
                    @csrf
                    <input type="hidden" name="message" value="Ticket marked as resolved by admin.">
                    <button name="resolve" value="1" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-2"
                            formaction="{{ route('web.tickets.reply', $ticket) }}">Mark resolved</button>
                </form>
            @endif
        </div>

        <div class="space-y-4">
            @foreach($ticket->replies as $reply)
                <div class="rounded-xl border border-slate-200 bg-white p-4 {{ $reply->user_id === auth()->id() ? '' : 'bg-slate-50' }}">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-[10px] font-bold">
                                {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                            </span>
                            <div>
                                <span class="text-sm font-medium text-slate-800">{{ $reply->user->name }}</span>
                                <span class="text-xs text-slate-400 ml-1">{{ $reply->user->unique_id }}</span>
                                @if($reply->user->isAdmin())
                                    <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-brand-100 text-brand-700 font-medium">Support</span>
                                @endif
                            </div>
                        </div>
                        <span class="text-xs text-slate-400">{{ $reply->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $reply->message }}</p>
                </div>
            @endforeach
        </div>

        @if(!in_array($ticket->status, ['resolved', 'closed']))
            <form method="POST" action="{{ route('web.tickets.reply', $ticket) }}" class="bg-white rounded-xl border border-slate-200 p-4">
                @csrf
                <label class="block text-sm font-medium text-slate-700 mb-2">Add reply</label>
                <textarea name="message" rows="3" required class="w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                <div class="mt-3 text-right">
                    <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2">Send</button>
                </div>
            </form>
        @endif
    </div>
@endsection
