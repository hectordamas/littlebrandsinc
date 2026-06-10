@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ isset($message) ? $message->embed(public_path('assets/img/logo-littlebrandsinc.png')) : asset('assets/img/logo-littlebrandsinc.png') }}" class="logo" alt="Little Brands Inc Logo" style="max-height: 55px; width: auto; object-fit: contain;">
</a>
</td>
</tr>
