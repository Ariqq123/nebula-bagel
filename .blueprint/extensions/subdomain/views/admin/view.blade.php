@extends('layouts.admin')

@section('title')
    Subdomain Manager
@endsection

@section('content-header')
    <h1>Subdomain Manager<small>Manage Cloudflare DNS subdomains for servers.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.extensions') }}">Extensions</a></li>
        <li class="active">Subdomain</li>
    </ol>
@endsection

@section('content')
@if($setup_required)
    {{-- Setup Required View --}}
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Setup Required</h3>
                </div>
                <div class="box-body text-center" style="padding: 40px;">
                    <p class="lead">No Cloudflare API tokens have been configured.</p>
                    <p class="text-muted">Add a Cloudflare API token to get started managing subdomains for your servers.</p>
                    <hr>
                    <form action="{{ route('admin.extensions.subdomain.post') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <input type="text" name="token" class="form-control" placeholder="Cloudflare API Token (40 characters)" maxlength="40" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" name="label" class="form-control" placeholder="Token Label (e.g. Production)" maxlength="100" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success btn-block"><i class="fa fa-plus"></i> Add Token</button>
                            </div>
                        </div>
                        <p class="text-muted small">The token must have <strong>Zone:Read</strong> and <strong>DNS:Edit</strong> permissions.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- Cache Staleness Warning --}}
    @if($cache_stale)
        <div class="row">
            <div class="col-xs-12">
                <div class="alert alert-warning">
                    <i class="fa fa-clock-o"></i>
                    <strong>Stale Cache:</strong> Zone data is {{ floor($cache_age / 60) }} minute(s) old. Cloudflare may be unavailable. Displaying cached data.
                </div>
            </div>
        </div>
    @endif

    {{-- Token Re-verification Warnings --}}
    @foreach($tokens as $token)
        @if(!empty($token['needs_re_verification']))
            <div class="row">
                <div class="col-xs-12">
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-circle"></i>
                        <strong>Token Needs Re-verification:</strong> Token "{{ $token['label'] }}" (****{{ $token['masked_token'] }}) returned an authentication error from Cloudflare and requires re-verification. Please remove and re-add this token.
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- Tabbed Interface --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab-tokens" data-toggle="tab"><i class="fa fa-key"></i> Tokens</a></li>
                    <li><a href="#tab-subdomains" data-toggle="tab"><i class="fa fa-globe"></i> Subdomains</a></li>
                    <li><a href="#tab-dashboard" data-toggle="tab"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                    <li><a href="#tab-settings" data-toggle="tab"><i class="fa fa-cog"></i> Settings</a></li>
                    <li><a href="#tab-audit" data-toggle="tab"><i class="fa fa-history"></i> Audit Log</a></li>
                </ul>
                <div class="tab-content">

                    {{-- ======================== --}}
                    {{-- TOKENS TAB --}}
                    {{-- ======================== --}}
                    <div class="tab-pane active" id="tab-tokens">
                        <div class="box box-primary" style="border-top: none; box-shadow: none;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Add New Token</h3>
                            </div>
                            <div class="box-body">
                                <form action="{{ route('admin.extensions.subdomain.post') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="token">API Token</label>
                                                <input type="text" id="token" name="token" class="form-control" placeholder="Cloudflare API Token (40 characters)" maxlength="40" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="label">Label</label>
                                                <input type="text" id="label" name="label" class="form-control" placeholder="e.g. Production DNS" maxlength="100" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-success btn-block"><i class="fa fa-plus"></i> Add Token</button>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted small">Token must have <strong>Zone:Read</strong> and <strong>DNS:Edit</strong> permissions. Only scoped API tokens are accepted (40 alphanumeric characters).</p>
                                </form>
                            </div>
                        </div>

                        <div class="box box-solid" style="box-shadow: none;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Configured Tokens</h3>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                @if(count($tokens) > 0)
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Label</th>
                                                <th>Token</th>
                                                <th>Added</th>
                                                <th>Status</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tokens as $token)
                                                <tr>
                                                    <td>{{ $token['label'] }}</td>
                                                    <td><code>****{{ $token['masked_token'] }}</code></td>
                                                    <td>{{ $token['added_at'] }}</td>
                                                    <td>
                                                        @if(!empty($token['needs_re_verification']))
                                                            <span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Needs Re-verification</span>
                                                        @else
                                                            <span class="label label-success"><i class="fa fa-check"></i> Active</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <form action="{{ route('admin.extensions.subdomain.delete', ['target' => 'token', 'id' => $token['token_id']]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this token?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-center" style="padding: 20px;">
                                        <p class="text-muted">No tokens configured yet.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ======================== --}}
                    {{-- SUBDOMAINS TAB --}}
                    {{-- ======================== --}}
                    <div class="tab-pane" id="tab-subdomains">
                        <div class="box box-primary" style="border-top: none; box-shadow: none;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Create Subdomain Record</h3>
                            </div>
                            <div class="box-body">
                                <form action="{{ route('admin.extensions.subdomain.put') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="subdomain">Subdomain Name</label>
                                                <input type="text" id="subdomain" name="subdomain" class="form-control" placeholder="e.g. mc1" maxlength="63" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="zone_id">Zone</label>
                                                <select id="zone_id" name="zone_id" class="form-control" required>
                                                    <option value="">Select Zone...</option>
                                                    @foreach($zones as $zone)
                                                        <option value="{{ $zone['id'] }}">{{ $zone['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="record_type">Record Type</label>
                                                <select id="record_type" name="record_type" class="form-control" required>
                                                    <option value="A">A</option>
                                                    <option value="AAAA">AAAA</option>
                                                    <option value="CNAME">CNAME</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="target">Target</label>
                                                <input type="text" id="target" name="target" class="form-control" placeholder="IP address or hostname" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="server_id">Server</label>
                                                <select id="server_id" name="server_id" class="form-control">
                                                    <option value="">No server (unassigned)</option>
                                                    @if(isset($servers))
                                                        @foreach($servers as $server)
                                                            <option value="{{ $server['id'] }}">{{ $server['name'] }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="proxied">Cloudflare Proxy</label>
                                                <select id="proxied" name="proxied" class="form-control">
                                                    <option value="0">Disabled (DNS only)</option>
                                                    <option value="1">Enabled (Proxied)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-md-offset-2">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-success btn-block"><i class="fa fa-plus"></i> Create Subdomain</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="box box-solid" style="box-shadow: none;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Existing Subdomain Records</h3>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                @if(count($records) > 0)
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>FQDN</th>
                                                <th>Type</th>
                                                <th>Target</th>
                                                <th>Server</th>
                                                <th>Proxied</th>
                                                <th>Created</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($records as $record)
                                                <tr>
                                                    <td><code>{{ $record['full_name'] }}</code></td>
                                                    <td><span class="label label-info">{{ $record['record_type'] }}</span></td>
                                                    <td><code>{{ $record['target'] }}</code></td>
                                                    <td>
                                                        @if($record['server_name'])
                                                            {{ $record['server_name'] }}
                                                        @else
                                                            <span class="text-muted"><em>Unassigned</em></span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($record['proxied'])
                                                            <span class="label label-warning"><i class="fa fa-cloud"></i> Yes</span>
                                                        @else
                                                            <span class="label label-default">No</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $record['created_at'] }}</td>
                                                    <td class="text-center">
                                                        <form action="{{ route('admin.extensions.subdomain.delete', ['target' => 'subdomain', 'id' => $record['record_id']]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this subdomain record? This will also remove the DNS record from Cloudflare.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-center" style="padding: 20px;">
                                        <p class="text-muted">No subdomain records have been created yet.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ======================== --}}
                    {{-- DASHBOARD TAB --}}
                    {{-- ======================== --}}
                    <div class="tab-pane" id="tab-dashboard">
                        <div class="box box-solid" style="border-top: none; box-shadow: none;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Subdomain-to-Server Mappings</h3>
                                <div class="box-tools">
                                    <form method="GET" action="{{ route('admin.extensions.subdomain') }}" class="form-inline" style="display: inline;">
                                        <select name="filter_server" class="form-control input-sm" style="width: 150px; display: inline-block;" onchange="this.form.submit()">
                                            <option value="">All Servers</option>
                                            @if(isset($servers))
                                                @foreach($servers as $server)
                                                    <option value="{{ $server['id'] }}" {{ request('filter_server') == $server['id'] ? 'selected' : '' }}>{{ $server['name'] }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <select name="filter_zone" class="form-control input-sm" style="width: 150px; display: inline-block;" onchange="this.form.submit()">
                                            <option value="">All Zones</option>
                                            @foreach($zones as $zone)
                                                <option value="{{ $zone['id'] }}" {{ request('filter_zone') == $zone['id'] ? 'selected' : '' }}>{{ $zone['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                @if(count($records) > 0)
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>FQDN</th>
                                                <th>Record Type</th>
                                                <th>Target</th>
                                                <th>Server</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                // Detect duplicate subdomains in same zone for conflict highlighting
                                                $subdomainZoneCounts = [];
                                                foreach ($records as $record) {
                                                    $key = $record['subdomain'] . '::' . $record['zone_id'];
                                                    if (!isset($subdomainZoneCounts[$key])) {
                                                        $subdomainZoneCounts[$key] = 0;
                                                    }
                                                    $subdomainZoneCounts[$key]++;
                                                }
                                            @endphp
                                            @foreach($records as $record)
                                                @php
                                                    $conflictKey = $record['subdomain'] . '::' . $record['zone_id'];
                                                    $isConflict = ($subdomainZoneCounts[$conflictKey] ?? 0) > 1;
                                                @endphp
                                                <tr class="{{ $isConflict ? 'danger' : '' }}">
                                                    <td>
                                                        <code>{{ $record['full_name'] }}</code>
                                                        @if($isConflict)
                                                            <span class="label label-danger" title="Duplicate subdomain in same zone"><i class="fa fa-exclamation-triangle"></i> Conflict</span>
                                                        @endif
                                                    </td>
                                                    <td><span class="label label-info">{{ $record['record_type'] }}</span></td>
                                                    <td><code>{{ $record['target'] }}</code></td>
                                                    <td>
                                                        @if($record['server_name'])
                                                            {{ $record['server_name'] }}
                                                        @else
                                                            <span class="label label-warning"><i class="fa fa-unlink"></i> Unassigned</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $record['created_at'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-center" style="padding: 20px;">
                                        <p class="text-muted">No subdomain mappings to display.</p>
                                    </div>
                                @endif
                            </div>
                            @if(isset($pagination) && $pagination['total_pages'] > 1)
                                <div class="box-footer with-border">
                                    <div class="text-center">
                                        <ul class="pagination" style="margin: 5px 0;">
                                            {{-- Previous Page --}}
                                            @if($pagination['current_page'] > 1)
                                                <li><a href="?page={{ $pagination['current_page'] - 1 }}&filter_server={{ request('filter_server') }}&filter_zone={{ request('filter_zone') }}">&laquo;</a></li>
                                            @else
                                                <li class="disabled"><span>&laquo;</span></li>
                                            @endif

                                            {{-- Page Numbers --}}
                                            @for($i = 1; $i <= $pagination['total_pages']; $i++)
                                                @if($i == $pagination['current_page'])
                                                    <li class="active"><span>{{ $i }}</span></li>
                                                @else
                                                    <li><a href="?page={{ $i }}&filter_server={{ request('filter_server') }}&filter_zone={{ request('filter_zone') }}">{{ $i }}</a></li>
                                                @endif
                                            @endfor

                                            {{-- Next Page --}}
                                            @if($pagination['current_page'] < $pagination['total_pages'])
                                                <li><a href="?page={{ $pagination['current_page'] + 1 }}&filter_server={{ request('filter_server') }}&filter_zone={{ request('filter_zone') }}">&raquo;</a></li>
                                            @else
                                                <li class="disabled"><span>&raquo;</span></li>
                                            @endif
                                        </ul>
                                        <p class="text-muted small">Showing page {{ $pagination['current_page'] }} of {{ $pagination['total_pages'] }} ({{ $pagination['total_records'] }} total records, 20 per page)</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ======================== --}}
                    {{-- SETTINGS TAB --}}
                    {{-- ======================== --}}
                    <div class="tab-pane" id="tab-settings">
                        <div class="box box-primary" style="border-top: none; box-shadow: none;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Extension Settings</h3>
                            </div>
                            <div class="box-body">
                                <form action="{{ route('admin.extensions.subdomain.patch') }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="rate_limit_per_minute">Rate Limit Per Minute</label>
                                                <input type="number" id="rate_limit_per_minute" name="rate_limit_per_minute" class="form-control" value="{{ $settings['rate_limit_per_minute']['value'] ?? 30 }}" min="{{ $settings['rate_limit_per_minute']['min'] ?? 1 }}" max="{{ $settings['rate_limit_per_minute']['max'] ?? 120 }}">
                                                <p class="text-muted small">Maximum Cloudflare API calls per minute. Default: {{ $settings['rate_limit_per_minute']['default'] ?? 30 }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="cache_ttl">Cache TTL (seconds)</label>
                                                <input type="number" id="cache_ttl" name="cache_ttl" class="form-control" value="{{ $settings['cache_ttl']['value'] ?? 300 }}" min="{{ $settings['cache_ttl']['min'] ?? 60 }}" max="{{ $settings['cache_ttl']['max'] ?? 3600 }}">
                                                <p class="text-muted small">How long zone data is cached before refreshing. Default: {{ $settings['cache_ttl']['default'] ?? 300 }}s</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="max_subdomains_per_server">Max Subdomains Per Server</label>
                                                <input type="number" id="max_subdomains_per_server" name="max_subdomains_per_server" class="form-control" value="{{ $settings['max_subdomains_per_server']['value'] ?? 5 }}" min="{{ $settings['max_subdomains_per_server']['min'] ?? 1 }}" max="{{ $settings['max_subdomains_per_server']['max'] ?? 50 }}">
                                                <p class="text-muted small">Maximum subdomain records per server. Default: {{ $settings['max_subdomains_per_server']['default'] ?? 5 }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="wildcard_allowed">Wildcard Subdomains</label>
                                                <select id="wildcard_allowed" name="wildcard_allowed" class="form-control">
                                                    <option value="0" {{ empty($settings['wildcard_allowed']['value']) ? 'selected' : '' }}>Disabled</option>
                                                    <option value="1" {{ !empty($settings['wildcard_allowed']['value']) ? 'selected' : '' }}>Enabled</option>
                                                </select>
                                                <p class="text-muted small">Allow wildcard (*) subdomain records. Default: {{ !empty($settings['wildcard_allowed']['default']) ? 'Enabled' : 'Disabled' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Settings</button>
                                            <p class="text-muted small" style="margin-top: 10px;">All settings are validated before saving. If any value is invalid, the entire update will be rejected and previous values preserved.</p>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ======================== --}}
                    {{-- AUDIT LOG TAB --}}
                    {{-- ======================== --}}
                    <div class="tab-pane" id="tab-audit">
                        <div class="box box-solid" style="border-top: none; box-shadow: none;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Audit Log</h3>
                                <div class="box-tools">
                                    <form method="GET" action="{{ route('admin.extensions.subdomain') }}" class="form-inline" style="display: inline;">
                                        <input type="hidden" name="tab" value="audit">
                                        <select name="audit_action" class="form-control input-sm" style="width: 180px; display: inline-block;" onchange="this.form.submit()">
                                            <option value="">All Actions</option>
                                            <option value="token_added" {{ request('audit_action') == 'token_added' ? 'selected' : '' }}>Token Added</option>
                                            <option value="token_removed" {{ request('audit_action') == 'token_removed' ? 'selected' : '' }}>Token Removed</option>
                                            <option value="subdomain_created" {{ request('audit_action') == 'subdomain_created' ? 'selected' : '' }}>Subdomain Created</option>
                                            <option value="subdomain_deleted" {{ request('audit_action') == 'subdomain_deleted' ? 'selected' : '' }}>Subdomain Deleted</option>
                                            <option value="settings_updated" {{ request('audit_action') == 'settings_updated' ? 'selected' : '' }}>Settings Updated</option>
                                            <option value="zone_refreshed" {{ request('audit_action') == 'zone_refreshed' ? 'selected' : '' }}>Zone Refreshed</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                @if(isset($audit_logs) && count($audit_logs) > 0)
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Timestamp</th>
                                                <th>Admin</th>
                                                <th>Action</th>
                                                <th>Resource</th>
                                                <th>Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($audit_logs as $log)
                                                <tr class="{{ $log['result'] === 'failure' ? 'danger' : '' }}">
                                                    <td><small>{{ $log['timestamp'] }}</small></td>
                                                    <td>{{ $log['admin_email'] }}</td>
                                                    <td>
                                                        @switch($log['action'])
                                                            @case('token_added')
                                                                <span class="label label-success">Token Added</span>
                                                                @break
                                                            @case('token_removed')
                                                                <span class="label label-danger">Token Removed</span>
                                                                @break
                                                            @case('subdomain_created')
                                                                <span class="label label-info">Subdomain Created</span>
                                                                @break
                                                            @case('subdomain_deleted')
                                                                <span class="label label-warning">Subdomain Deleted</span>
                                                                @break
                                                            @case('settings_updated')
                                                                <span class="label label-primary">Settings Updated</span>
                                                                @break
                                                            @case('zone_refreshed')
                                                                <span class="label label-default">Zone Refreshed</span>
                                                                @break
                                                            @default
                                                                <span class="label label-default">{{ $log['action'] }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        <small>{{ $log['resource_type'] }}: {{ $log['resource_id'] }}</small>
                                                    </td>
                                                    <td>
                                                        @if($log['result'] === 'success')
                                                            <span class="label label-success">Success</span>
                                                        @else
                                                            <span class="label label-danger">Failed</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-center" style="padding: 20px;">
                                        <p class="text-muted">No audit log entries found.</p>
                                    </div>
                                @endif
                            </div>
                            @if(isset($audit_pagination) && $audit_pagination['total_pages'] > 1)
                                <div class="box-footer with-border">
                                    <div class="text-center">
                                        <ul class="pagination" style="margin: 5px 0;">
                                            @if($audit_pagination['current_page'] > 1)
                                                <li><a href="?tab=audit&audit_page={{ $audit_pagination['current_page'] - 1 }}&audit_action={{ request('audit_action') }}">&laquo;</a></li>
                                            @else
                                                <li class="disabled"><span>&laquo;</span></li>
                                            @endif

                                            @for($i = 1; $i <= $audit_pagination['total_pages']; $i++)
                                                @if($i == $audit_pagination['current_page'])
                                                    <li class="active"><span>{{ $i }}</span></li>
                                                @else
                                                    <li><a href="?tab=audit&audit_page={{ $i }}&audit_action={{ request('audit_action') }}">{{ $i }}</a></li>
                                                @endif
                                            @endfor

                                            @if($audit_pagination['current_page'] < $audit_pagination['total_pages'])
                                                <li><a href="?tab=audit&audit_page={{ $audit_pagination['current_page'] + 1 }}&audit_action={{ request('audit_action') }}">&raquo;</a></li>
                                            @else
                                                <li class="disabled"><span>&raquo;</span></li>
                                            @endif
                                        </ul>
                                        <p class="text-muted small">Showing page {{ $audit_pagination['current_page'] }} of {{ $audit_pagination['total_pages'] }} (25 entries per page)</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('footer-scripts')
    @parent
    <script>
        $(function () {
            // Activate tab from URL hash or query parameter
            var tab = '{{ request("tab", "") }}';
            if (tab) {
                $('.nav-tabs a[href="#tab-' + tab + '"]').tab('show');
            } else if (window.location.hash) {
                $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
            }

            // Update hash on tab change
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                window.location.hash = e.target.hash;
            });
        });
    </script>
@endsection
