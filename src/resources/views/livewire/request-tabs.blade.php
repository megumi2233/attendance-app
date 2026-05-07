<div>
    <div class="request-tabs">
        <a href="#" wire:click.prevent="changeTab('pending')" class="request-tab {{ $tab === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="#" wire:click.prevent="changeTab('approved')" class="request-tab {{ $tab === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    @if ($tab === 'pending')
        <table class="request-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingRequests as $request)
                    <tr>
                        <td>{{ $request->status }}</td>
                        <td>{{ $request->attendance->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->date)->format('Y/m/d') }}</td>
                        <td>{{ $request->reason }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                        <td>
                            @if (Auth::guard('admin')->check())
                                <a href="/stamp_correction_request/approve/{{ $request->id }}" class="detail-link">詳細</a>
                            @else
                                <a href="/attendance/detail/{{ $request->attendance_id }}" class="detail-link">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($tab === 'approved')
        <table class="request-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($approvedRequests as $request)
                    <tr>
                        <td>{{ $request->status }}</td>
                        <td>{{ $request->attendance->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->date)->format('Y/m/d') }}</td>
                        <td>{{ $request->reason }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                        <td>
                            @if (Auth::guard('admin')->check())
                                <a href="/stamp_correction_request/approve/{{ $request->id }}" class="detail-link">詳細</a>
                            @else
                                <a href="/attendance/detail/{{ $request->attendance_id }}" class="detail-link">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
