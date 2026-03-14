@extends('waiver.layout')

@section('title', 'Waiver link expired')

@section('content')
    <div class="rounded-lg border border-sidebar-border bg-card p-6 shadow-sm">
        <h1 class="text-xl font-semibold">Link expired</h1>
        <p class="mt-2 text-muted-foreground">This waiver link for {{ $customerName }} has expired. Please request a new link from the shop.</p>
    </div>
@endsection
