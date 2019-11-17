@extends('app')

@section('content')
    <table class="table table-hover table-dark" style="margin: 0px auto; margin-top: 13%;">
        <thead>
        <tr>
            <th scope="col"># Batch ID</th>
            <th scope="col">Mail Service Used</th>
            <th scope="col">Initially Processed At</th>
            <th scope="col">Last Retried At</th>
            <th scope="col">Select Retry Service</th>
            <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($batches as $batch)
            <form action="/settings/retry-batch" METHOD="POST">
                <input type="hidden" name="batch_id" value="{{$batch->id}}">
                <tr aria-disabled="true">
                    <th scope="row">{{$batch->batch_message_hash}}</th>
                    <td>{{ucfirst($batch->service)}}</td>
                    <td>{{$batch->created_at->diffForHumans()}}</td>
                    <td class="text-center">
                        {{is_null($batch->retried_at) ? '---' : $batch->retried_at->format('m-d-Y')}}
                    </td>
                    <td>
                        <div class="input-group">
                            <select name="service" class="custom-select" id="inputGroupSelect01" {{is_null($batch->retried_at) ? '' : 'disabled'}}>
                                <option disabled>Choose...</option>
                                <option {{$batch->service === 'mailgun' ? 'disabled' : ''}} value="mailgun">Mailgun</option>
                                <option {{$batch->service === 'pepipost' ? 'disabled' : ''}} value="pepipost">Pepipost</option>
                                <option {{$batch->service === 'sendgrid' ? 'disabled' : ''}} value="sendgrid">Sendgrid</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <button type="submit" {{is_null($batch->retried_at) ? '' : 'disabled'}} class="btn btn-{{is_null($batch->retried_at) ? 'success' : 'secondary'}}">
                            {{is_null($batch->retried_at) ? 'Retry' : 'Retried'}}
                        </button>
                    </td>
                </tr>
            </form>
        @endforeach
        </tbody>
    </table>
@endsection