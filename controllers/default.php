<?php

class DefaultController extends Controller
{
   function index()
   {
		echo "I'm just a default.";
		$this->no_view_file = true;
   }
}