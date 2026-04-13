@php
    $csrfToken = csrf_token();
@endphp
@csrf
<input type="hidden" name="CSRFToken" value="{{ $csrfToken }}">
<input type="hidden" name="OWASP_CSRFTOKEN" value="{{ $csrfToken }}">
<input type="hidden" name="anticsrf" value="{{ $csrfToken }}">
