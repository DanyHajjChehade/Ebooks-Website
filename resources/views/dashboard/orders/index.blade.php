@extends('dashboard.layout.layout')

@section('body')


    <div class="card">
        <div class="card-header">
            <h5>Latest Orders</h5>
        </div>
        <div class="card-body">
            <div class="user-status table-responsive latest-order-table">
                @if ($orders->isEmpty())
                        <br><br><br><br>
                        <center><b><h3>There is no orders right now.Please come back later.</h3></b></center>
                        <br><br><br><br>
                @else
                <table class="table table-bordernone">
                    <thead>
                        <tr>
                            <th scope="col">Order ID</th>
                            <th scope="col">Order Total</th>
                            <th scope="col">Payment Method</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td class="digits">${{ $order->total_price }}</td>
                            <td class="font-primary">{{ $order->payment_method }}</td>
                            <td class="digits">{{ $order->status }}</td>
                            <td class="d-flex">


                                    <form action="{{ route('dashboard.order.cancel',['orderid' => $order->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class=" btn btn-primary" @if($order->status === 'Canceled' || $order->status === 'Delivered') disabled @endif>Cancel Order</button>
                                </form>
                                    <br>
                                <form action="{{ route('dashboard.order.deliver',['orderid' => $order->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="ms-2 btn btn-success" @if($order->status === 'Canceled' || $order->status === 'Delivered') disabled @endif>Delivered</button>
                                </form>
                                @if($order->status === 'Canceled' || $order->status === 'Delivered')
                            <form class="ms-2"  action="{{ route('dashboard.order.delete',['orderid' => $order->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class=" btn btn-danger " ><i class="fa fa-trash"></i></button>
                            </form>
                            @endif

                        </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>


 @endsection
