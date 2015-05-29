@extends('app')

@section('content')

    <div class="container">

        <div class="row">
            <div  class="col-md-9" ><h1 class="page-header">Netblocks</h1></div>
            <div  class="col-md-3 pagination">
                {!! link_to_route('admin.netblocks.create', 'Create Netblock', '', array('class' => 'btn btn-info')) !!}
                {!! link_to_route('admin.export.netblocks', 'CSV Export', array('format' => 'csv'), array('class' => 'btn btn-info')) !!}
            </div>
        </div>

        @if ( !$netblocks->count() )
            You have no netblocks yet
        @else
            {!! $netblocks->render() !!}

            <table class="table table-striped table-condensed">
                <thead>
                <tr>
                    <th>Contact</th>
                    <th>First IP</th>
                    <th>Last IP</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>

                @foreach( $netblocks as $netblock )
                    <tr>
                        <td>{{ $netblock->contact->name }} ({{ $netblock->contact->reference }})</td>
                        <td>{{ inet_ntop($netblock->first_ip) }}</td>
                        <td>{{ inet_ntop($netblock->last_ip) }}</td>
                        <td>
                            {!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('admin.netblocks.destroy', $netblock->id))) !!}
                            {!! link_to_route('admin.netblocks.show', 'Details', array($netblock->id), array('class' => 'btn btn-info')) !!}
                            {!! link_to_route('admin.netblocks.edit', 'Edit', array($netblock->id), array('class' => 'btn btn-info')) !!}
                            {!! Form::submit('Delete', array('class' => 'btn btn-danger')) !!}
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif

    </div>

@endsection