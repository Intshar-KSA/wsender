<x-filament::page>
    <x-filament::section>
    <x-slot name="heading">Subscription Details</x-slot>

    <dl class="grid grid-cols-2 gap-4">
        <dt>User</dt>
        <dd>{{ $record->user->name ?? '-' }}</dd>

        <dt>Device</dt>
        <dd>{{ $record->device->nickname ?? '-' }}</dd>

        <dt>Plan</dt>
        <dd>{{ $record->plan->title ?? '-' }}</dd>

        <dt>Start Date</dt>
        <dd>{{ $record->start_date }}</dd>

        <dt>Payment Method</dt>
        <dd>{{ ucfirst($record->payment_method) }}</dd>

        <dt>Payment Status</dt>
        <dd>{{ ucfirst($record->payment_status) }}</dd>

        <dt>Receipt</dt>
        <dd>
            @if($record->receipt_url)
                <a href="{{ asset('storage/' . $record->receipt_url) }}" target="_blank" class="text-primary-600 underline">
                    View Receipt
                </a>
            @else
                No receipt uploaded
            @endif
        </dd>
    </dl>
</x-filament::section>

</x-filament::page>
