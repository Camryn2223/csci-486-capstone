<div class="mb-25">
    <label>Template Name</label>
    <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}" class="w-full mb-0" required>
</div>

<h3 class="fs-16 mt-0 mb-10 text-primary">Applicant Information Fields</h3>
<p class="text-muted mt-0 mb-15 fs-14">Choose which applicant information to collect.</p>

<div class="grid-perms mb-0">
    <label class="text-light cursor-pointer items-center flex-gap-10">
        <input type="checkbox" name="request_name" value="1" {{ old('request_name', $template->request_name ?? true) ? 'checked' : '' }}> Full Name
    </label>
    <label class="text-light cursor-pointer items-center flex-gap-10">
        <input type="checkbox" name="request_email" value="1" {{ old('request_email', $template->request_email ?? true) ? 'checked' : '' }}> Email Address
    </label>
    <label class="text-light cursor-pointer items-center flex-gap-10">
        <input type="checkbox" name="request_phone" value="1" {{ old('request_phone', $template->request_phone ?? true) ? 'checked' : '' }}> Phone Number
    </label>
</div>