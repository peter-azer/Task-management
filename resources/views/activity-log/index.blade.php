@extends('layout.page')

@section('app-header')
    Activity Log
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Activity Log</h1>
            <p class="text-gray-600 mt-1">View all system activities and changes</p>
        </div>

        <div class="p-6">
            @if($activities->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date & Time
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Subject
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($activities as $activity)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $activity->created_at->format('M j, Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $activity->causer?->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $activity->description == 'created' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $activity->description == 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $activity->description == 'deleted' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ !in_array($activity->description, ['created', 'updated', 'deleted']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ ucfirst($activity->description) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if($activity->subject)
                                            {{ class_basename($activity->subject_type) }}: {{ $activity->subject->getActivityDisplayName() }}
                                        @elseif($activity->subject_type)
                                            {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }} (deleted)
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>

                                @if($activity->properties && $activity->properties->count() > 0)
                                    <tr class="bg-gray-50">
                                        <td colspan="5" class="px-6 py-3">
                                            <details class="cursor-pointer">
                                                <summary class="text-sm font-medium text-gray-700 hover:text-gray-900">
                                                    View Details
                                                </summary>
                                                <div class="mt-2 p-3 bg-white rounded border border-gray-200">
                                                    <pre class="text-xs text-gray-600 whitespace-pre-wrap">{{ json_encode($activity->properties->toArray(), JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </details>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $activities->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No activities found</h3>
                    <p class="mt-1 text-sm text-gray-500">No activity logs have been recorded yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
