<h1 class="p-relative">Profile</h1>
<div class="profile-page m-20">
    <!-- Start Overview -->
    <x-member.overview :user="$user"/>
    <!-- end overview-->

    <!-- Insert information form -->
    <x-member.insertForm :user="$user"/>
    <!-- end insert information form-->
</div>
