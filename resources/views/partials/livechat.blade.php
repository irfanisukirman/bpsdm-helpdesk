@php
    $lc = config('helpdesk.livechat', []);
    $tawkEnabled = ($lc['enabled'] ?? false)
        && ($lc['provider'] ?? null) === 'tawkto'
        && ! empty($lc['tawkto']['property_id']);
@endphp

@if ($tawkEnabled)
    {{-- Live Chat (Tawk.to) — hanya area publik, PRD Bagian 13.2 --}}
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {};
        var Tawk_LoadStart = new Date();
        (function () {
            var s1 = document.createElement("script"),
                s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/{{ $lc['tawkto']['property_id'] }}/{{ $lc['tawkto']['widget_id'] }}';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
@endif
