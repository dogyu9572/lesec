@extends('backoffice.layouts.app')

@section('title', '예약 캘린더')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/reservation-calendar.css') }}">
@endsection

@section('content')
<div class="board-container">
    <div class="board-card">
<div class="board-card-body">
            <div class="schedule_wrap">
                <!-- 왼쪽: 캘린더 -->
                <div class="schedule_table">
                    <div class="schedule_top">
                        <div class="month">
                            <button type="button" class="arrow prev" data-year="{{ $year }}" data-month="{{ $month }}">이전달</button>
                            <strong>{{ $year }}.{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</strong>
                            <button type="button" class="arrow next" data-year="{{ $year }}" data-month="{{ $month }}">다음달</button>
                            <button type="button" class="btn_today">당일보기</button>
                        </div>
                        <ul class="info flex">
                            <li class="i_select">선택날짜</li>
                            <li class="i_possible">예약가능</li>
                            <li class="i_impossible">예약마감</li>
                        </ul>
                    </div>
                    <div class="table">
                        <table>
                            <thead>
                                <tr>
                                    <th>일</th>
                                    <th>월</th>
                                    <th>화</th>
                                    <th>수</th>
                                    <th>목</th>
                                    <th>금</th>
                                    <th>토</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($calendar as $week)
                                <tr>
                                    @foreach($week as $dayData)
                                    @php
                                        $dateStr = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($dayData['day'], 2, '0', STR_PAD_LEFT);
                                        $isSelected = request()->input('date') === $dateStr;
                                        $tdClasses = ['calendar-day'];
                                        if ($dayData['disabled']) {
                                            $tdClasses[] = 'disabled';
                                        } else {
                                            $tdClasses[] = 'clickable';
                                        }
                                        if ($isSelected) {
                                            $tdClasses[] = 'selected';
                                        }
                                        $summary = $dayData['reservation_summary'] ?? ['individual' => 0, 'group' => 0, 'total' => 0];
                                        $reservationsForDate = $reservationsByDate[$dateStr] ?? ['individual' => [], 'group' => []];
                                    @endphp
                                    <td class="{{ implode(' ', $tdClasses) }}" data-date="{{ $dateStr }}">
                                        <span>{{ $dayData['day'] }}</span>
                                        @if(!empty($dayData['is_disabled_date']))
                                            <div class="schedule-disabled">예약불가</div>
                                        @elseif(!$dayData['disabled'] && ($summary['individual'] > 0 || $summary['group'] > 0))
                                            <ul class="list">
                                                @foreach($reservationsForDate['individual'] as $reservation)
                                                @php
                                                    $statusClass = ($reservation['is_closed'] ?? false) ? 'i_impossible' : 'i_possible';
                                                @endphp
                                                <li class="{{ $statusClass }}">
                                                    {{ \Illuminate\Support\Str::limit($reservation['program_name'], 20) }}
                                                </li>
                                                @endforeach
                                                @foreach($reservationsForDate['group'] as $reservation)
                                                @php
                                                    $statusClass = ($reservation['is_closed'] ?? false) ? 'i_impossible' : 'i_possible';
                                                @endphp
                                                <li class="{{ $statusClass }}">
                                                    {{ \Illuminate\Support\Str::limit($reservation['program_name'], 20) }}
                                                </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 오른쪽: 예약 내역 -->
                <div class="glbox select_day">
                    <div class="top">
                        <dl class="day">
                            <dt>선택일자</dt>
                            <dd id="selected-date-display">날짜를 선택하세요</dd>
                        </dl>
                        <div class="scroll tbl">
                            <!-- 개인 예약 -->
                            <div id="individual-section" style="margin-bottom: 20px;">
                                <h6 style="margin-bottom: 10px;">
                                    개인
                                    <span id="individual-count" class="badge badge-danger" style="display: none;">0</span>
                                </h6>
                                <table>
                                    <colgroup>
                                        <col class="w80"/>
                                        <col/>
                                        <col class="w100">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>일자</th>
                                            <th>프로그램명</th>
                                            <th>인원/정원</th>
                                        </tr>
                                    </thead>
                                    <tbody id="individual-reservations">
                                        <tr>
                                            <td colspan="3" class="text-center" style="padding: 40px;">날짜를 선택하세요</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 단체 예약 -->
                            <div id="group-section">
                                <h6 style="margin-bottom: 10px;">
                                    단체
                                    <span id="group-count" class="badge badge-danger" style="display: none;">0</span>
                                </h6>
                                <table>
                                    <colgroup>
                                        <col class="w80"/>
                                        <col/>
                                        <col class="w100">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>일자</th>
                                            <th>프로그램명</th>
                                            <th>인원/정원</th>
                                        </tr>
                                    </thead>
                                    <tbody id="group-reservations">
                                        <tr>
                                            <td colspan="3" class="text-center" style="padding: 40px;">날짜를 선택하세요</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="reservation-calendar-config" 
     data-reservations-url="{{ route('backoffice.reservation-calendar.reservations') }}"
     data-base-url="{{ route('backoffice.reservation-calendar.index') }}"
     style="display: none;"></div>

@once
@push('scripts')
<script src="{{ asset('js/backoffice/reservation-calendar.js') }}"></script>
@endpush
@endonce

@endsection

