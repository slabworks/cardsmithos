@extends('waiver.layout')

@section('title', 'Card Repair Service Waiver')

@section('content')
    <div class="rounded-lg border border-sidebar-border bg-card p-6 shadow-sm">
        <h1 class="text-xl font-semibold">Service Waiver</h1>
        <p class="mt-1 text-sm text-muted-foreground">Please review and sign for {{ $customer->name }}.</p>

        <div class="mt-6 rounded-md bg-muted/50 p-4 text-sm">
            {!! nl2br(e($agreementText)) !!}
        </div>

        @if (session('error'))
            <p class="mt-4 text-sm text-destructive">{{ session('error') }}</p>
        @endif

        <form method="POST" action="{{ request()->fullUrl() }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="signer_name" class="block text-sm font-medium">Your name</label>
                <input type="text" name="signer_name" id="signer_name" value="{{ old('signer_name', $customer->name) }}"
                    class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    required autofocus>
                @error('signer_name')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="signer_email" class="block text-sm font-medium">Your email</label>
                <input type="email" name="signer_email" id="signer_email" value="{{ old('signer_email', $customer->email) }}"
                    class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    required>
                @error('signer_email')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="agreed" id="agreed" value="1" {{ old('agreed') ? 'checked' : '' }}
                    class="rounded border-input">
                <label for="agreed" class="text-sm">I have read and agree to the terms above.</label>
            </div>
            @error('agreed')
                <p class="text-sm text-destructive">{{ $message }}</p>
            @enderror
            <div class="pt-2">
                <button type="submit"
                    class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    Submit waiver
                </button>
            </div>
        </form>
    </div>
@endsection
