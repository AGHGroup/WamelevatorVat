@extends('layouts.app')

@section('title', $table)
@section('page-title', $table)

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('oracle.tables') }}">{{ __('lce.breadcrumb') }}</a>
  </li>
  <li class="breadcrumb-item active">{{ $table }}</li>
@endsection

@section('page-actions')
  <a href="{{ route('oracle.tables') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bx bx-arrow-back me-1"></i>
    {{ __('lce.back_to_tables') }}
  </a>
@endsection

@section('content')

  <div class="row g-3 mb-4">
    <div class="col-auto">
      <div class="card border-0 shadow-sm px-4 py-3 d-flex flex-row align-items-center gap-3">
        <span class="avatar avatar-sm bg-label-primary rounded">
          <i class="bx bx-columns"></i>
        </span>
        <div>
          <div class="fw-semibold">{{ count($columns) }}</div>
          <small class="text-body-secondary">{{ __('lce.columns') }}</small>
        </div>
      </div>
    </div>
    <div class="col-auto">
      <div class="card border-0 shadow-sm px-4 py-3 d-flex flex-row align-items-center gap-3">
        <span class="avatar avatar-sm bg-label-success rounded">
          <i class="bx bx-list-ul"></i>
        </span>
        <div>
          <div class="fw-semibold">{{ $rows->total() }}</div>
          <small class="text-body-secondary">{{ __('lce.records') }}</small>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-datatable table-responsive">
      @if ($rows->isEmpty())
        <div class="text-center py-5 text-body-secondary">
          <i class="bx bx-data display-4 d-block mb-2"></i>
          {{ __('lce.no_rows') }}
        </div>
      @else
        <table class="table table-hover table-sm mb-0">
          <thead class="table-dark">
            <tr>
              @foreach ($columns as $col)
                @if ($col->COLUMN_NAME !== 'RN')
                  <th class="text-nowrap small" title="{{ $col->DATA_TYPE }}">
                    {{ $col->COLUMN_NAME }}
                  </th>
                @endif
              @endforeach
            </tr>
          </thead>
          <tbody>
            @foreach ($rows as $row)
              <tr>
                @foreach ($columns as $col)
                  @if ($col->COLUMN_NAME !== 'RN')
                    <td class="small text-nowrap"
                        style="max-width:220px;overflow:hidden;text-overflow:ellipsis;"
                        title="{{ $row->{$col->COLUMN_NAME} ?? '' }}">
                      {{ $row->{$col->COLUMN_NAME} ?? '' }}
                    </td>
                  @endif
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
        <div class="p-3">{{ $rows->links() }}</div>
      @endif
    </div>
  </div>

@endsection
