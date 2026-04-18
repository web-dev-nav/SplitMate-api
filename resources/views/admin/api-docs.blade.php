@extends('admin.layout', [
    'title' => 'API Access',
    'subtitle' => 'Operational endpoints used by the iOS client.',
])

@section('content')
    <div class="grid two">
        <div class="panel">
            <h2>Base URL</h2>
            <p class="muted">Configure the iOS app to target this backend.</p>
            <div class="code" style="display: inline-block; margin-top: 8px;">{{ $baseUrl }}</div>
        </div>

        <div class="panel">
            <h2>Authentication</h2>
            <p class="muted">The mobile app uses Laravel Sanctum bearer tokens. Authenticate with <span class="code">/auth/login</span>, store the token on device, then send it as an <span class="code">Authorization: Bearer ...</span> header.</p>
        </div>
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>Endpoints</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>URI</th>
                    <th>Purpose</th>
                </tr>
            </thead>
            <tbody>
                @foreach($endpoints as $endpoint)
                    <tr>
                        <td><span class="badge primary">{{ $endpoint['method'] }}</span></td>
                        <td><span class="code">{{ $endpoint['uri'] }}</span></td>
                        <td>{{ $endpoint['description'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
