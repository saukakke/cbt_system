<?php
namespace Tests\Feature;
use Tests\TestCase;
class HomeTest extends TestCase { public function test_guest_can_view_home():void{$this->get('/')->assertOk();} public function test_login_page_is_public():void{$this->get('/login')->assertOk();} }
