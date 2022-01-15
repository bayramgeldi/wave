@extends('theme::layouts.app')

@section('content')
    <div class="py-20 mx-auto text-center max-w-7xl">
        <div class="w-full space-y-2">
            <table class="table-auto border-collapse border border-gray-400">
                <thead>
                <tr>
                    <th class="border border-gray-300">Order Number</th>
                    <th class="border border-gray-300">Order Date</th>
                    <th class="border border-gray-300">Shipment Type</th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td class="border border-gray-300 text-left">{{ $order->number }}</td>
                        <td class="border border-gray-300">{{ $order->date }}</td>
                        <td class="border border-gray-300 text-left">{{ $order->delivery_address_type }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection