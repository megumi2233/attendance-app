<div> {{-- 🚨 必須：Livewire部品は全体を1つのdivで囲む！ --}}

    <div class="request-tabs">
        {{-- 🌟 href の代わりに wire:click="changeTab(...)" を使うことで、画面がチカッとせずに裏側で切り替わります！ --}}
        <a href="#" wire:click.prevent="changeTab('pending')"
            class="request-tab {{ $tab === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="#" wire:click.prevent="changeTab('approved')"
            class="request-tab {{ $tab === 'approved' ? 'active' : '' }}">承認済み</a>
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
                        <td><a href="/attendance/detail/{{ $request->attendance_id }}" class="detail-link">詳細</a></td>
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
                        <td><a href="/attendance/detail/{{ $request->attendance_id }}" class="detail-link">詳細</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>
