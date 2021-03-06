@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">

<div class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading">Room Sales</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Sub Total</th>
                        </tr>
                    </thead>
                    <tbody>


                        @foreach ($sales as $key => $sale)

                                @foreach($sale->items as $item)
                                    @if($item->product->name == 'Additional Payment' || $item->price == 0 || $item->quantity == 0 )
                                    @else
                                    <tr>

                                        <td>{{ $item->product->name }}</td>
                                        <td>₱ {{ number_format($item->price,2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>₱ {{ number_format($item->quantity * $item->price,2) }}</td>
                                    @endif
                                    </tr>
                                @endforeach

                        @endforeach
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>₱ {{$saleItem}}</td>
                                    </tr>

                    </tbody>
                </table>
            </div>
        </div>


        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">Room Details - <strong>({{$sales[0]->roomType}})</strong></div>

                <div class="panel-body">
                <br>
                <div><strong>Type</strong><p class="pull-right">{{$sales[0]->promoType}}</p></div>
                <br>
                <div> <strong>Customer Details</strong><p class="pull-right">
                @foreach($sales as $sale)
                    @if($sale->movies == 'None')
                    @else
                    {{$sale->movies}},
                    @endif
                @endforeach
                </p></div>
                <br>

                <div><strong>Time</strong><p class="pull-right">{{$startTime}} to {{$endTime}}</p></div>
                </div>
            </div>
        </div>


        <div class="col-md-2">
            <div class="panel panel-default">
                    <div class="panel-body">                
                    <br><br>
                    <form action="{{ url('rooms/' . $customer->id) }}" method="POST">
                        <input type="hidden" name="_method" value="put">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <input type="hidden" class="form-control" id="name" name="name" value="{{ old('name', $customer->name) }}">
                        </div>
                        <div class="form-group">
                            <input type="hidden" class="form-control" id="status" name="status" value="Available">
                       </div>
    
                        <div class="form-group">
                            <input type="hidden" class="form-control" id="session" name="session" value="{{$sessionId}}">
                       </div>

                       <div align="center" >
                            <button type="submit" class="btn btn-primary" >End Reservation</button>
                       </div>
                    </form>
                    <br><br>
                    </div>
            </div>
        </div>

        



<!--         <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading"><center>Total</center></div>
                    <center>
                    <h2>₱ {{$saleItem}}</h2>
                    </center>
            </div>
        </div> -->




    </div>

</div>
@endsection