@php
	$t = csrf_token();
@endphp
<input type="hidden" name="OWASP_CSRFTOKEN" value="{{ $t }}" autocomplete="off">
<input type="hidden" name="anticsrf" value="{{ $t }}" autocomplete="off">
<input type="hidden" name="CSRFToken" value="{{ $t }}" autocomplete="off">
<input type="hidden" name="_token" value="{{ $t }}" autocomplete="off">
