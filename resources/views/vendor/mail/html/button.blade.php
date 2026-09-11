@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
{{-- Der Rahmen auf dem Link ersetzt Innenabstände, weil Outlook Padding an Links ignoriert. --}}
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}" class="action-cell">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}" class="button-cell" bgcolor="#18181b">
<a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener">{!! $slot !!}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
