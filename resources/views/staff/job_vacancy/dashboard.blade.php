@extends('staff.layouts.app')

@section('content')

{{-- STAT CARDS
     These are the quick links. There used to be a second row of link cards
     under the calendar that went to the same two pages, so the desk was shown
     a number and, further down, a separate card that went to where the number
     came from. Making the number itself the link removes that split: the count
     you read is the list you land on. --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-4">
        <a href="{{ route('staff.employers', ['tab' => 'pre']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div class="fs-2 fw-bold" style="color:var(--warn);">{{ $pendingCount }}</div>
                <div class="text-muted small">Pending Company Requirements</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('staff.employers', ['tab' => 'approved']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div class="fs-2 fw-bold" style="color:var(--g-700);">{{ $approvedCount }}</div>
                <div class="text-muted small">Total Number of Company</div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-4">
        {{-- Lands on the same Approved Employers tab, but filtered down to the
             companies whose papers run out within the week. --}}
        <a href="{{ route('staff.employers', ['tab' => 'approved', 'filter' => 'expiring']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div class="fs-2 fw-bold" style="color:var(--danger);">{{ $expiringCount }}</div>
                <div class="text-muted small">Nearly Expired Requirements</div>
            </div>
        </a>
    </div>
</div>

@include('partials.activity-calendar')

@endsection