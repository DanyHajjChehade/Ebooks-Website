<tr>
    <td>{{ $cartitem->name}}</td>
    <td>
        <input  type="number" value="{{ $cartitem->qty }}" min="1" max="{{ $cartitem->options->copies_owned }}" class="form-control" readonly>
    </td>
    <td>${{ $cartitem->price }}</td>
    <td>{{ $cartitem->price * $cartitem->qty }}$</td>
    <td>
        <form method="POST" action="{{ route('cart.remove',$cartitem->rowId) }}">
            @csrf
            @method('DELETE')
        <button type="submit" class="btn btn-danger" >Remove</button>
        </form>
    </td>
</tr>
