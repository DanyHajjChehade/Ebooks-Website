<h1 class="p-relative">Manage:</h1>
<div class="projects p-20 bg-white rad-10 m-20">
    <h2 class="mt-0 mb-20 d-flex between-flex">Orders
    </h2>
    <div class="responsive-table">
        @if ($orders->isEmpty())
                        <br><br><br><br>
                        <center><b><h3>There is no orders right now.Please come back later.</h3></b></center>
                        <br><br><br><br>
                @else
        <table class="fs-15 w-full">
            <thead>
                <tr>
                    <td>Order Date</td>
                    <td>Total Price</td>
                    <td>Payment Method</td>
                    <td>Address</td>
                    <td>Status</td>
                    <td>Action</td>
                </tr>
            </thead>
            <tbody>
                @foreach ( $orders as $order )
                <tr>
                    <td>
                        {{ $order->created_at->diffForHumans() }}
                    </td>
                    <td>
                        ${{ $order->total_price }}
                    </td>
                    <td>{{ $order->payment_method }}</td>
                    <td> {{ auth()->user()->address }}</td>
                    <td>{{ $order->status }}</td>
                    <td>
                        <form method="POST" action="{{ route('user.order.cancel',['user'=>auth()->user(),'orderid'=>$order->id]) }}">
                            @csrf
                        <button type="submit" class="btndel label btn-shape bg-red c-white"@if($order->status === 'Canceled') disabled @endif>Cancel Order</button>
                        </form>
                        <br>
                        @if($order->status === 'Canceled')
                            <form class="ms-2"  action="{{ route('user.order.delete',['user'=>auth()->user(),'orderid'=>$order->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-blue label btn-shape c-white"><i class="fa fa-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
        @endif
    </div>
</div>
<style>
    .btndel {
        background-color: #ff0000; /* Red background */
        color: #ffffff; /* White text */
        border: none;
        padding: 10px 20px;
        cursor: pointer;
    }

    .btndel:disabled {
        background-color: #cccccc; /* Light gray background */
        color: #999999; /* Dark gray text */
        cursor: not-allowed; /* Change cursor to indicate not clickable */
    }

    /* Button for deleting canceled orders */
    .btn-blue {
        background-color: #007bff; /* Blue background */
        color: #ffffff; /* White text */
        border: none;
        padding: 10px 20px;
        cursor: pointer;
    }

    .btn-blue:hover {
        background-color: #0069d9; /* Darker blue on hover */
    }

    .btn-blue:focus {
        box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.5); /* Blue focus outline */
    }

    /* Adjust FontAwesome icon size and color */
    .fa-trash {
        margin-right: 5px; /* Margin between icon and text */
        font-size: 18px; /* Icon size */
        color: #ffffff; /* White icon color */
    }

</style>
