@extends('waiver.layout')

@section('title', 'Waiver already signed')

@section('content')
    <div class="rounded-lg border border-sidebar-border bg-card p-6 shadow-sm">
        @if ($justSigned ?? null)
            <h1 class="text-xl font-semibold">Thank you</h1>
            <p class="mt-2 text-muted-foreground">{{ $justSigned }}</p>
        @else
            <h1 class="text-xl font-semibold">Already signed</h1>
            <p class="mt-2 text-muted-foreground">The waiver for {{ $customerName }} has already been signed. No further action is needed.</p>
        @endif
    </div>
@endsection
