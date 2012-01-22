<?php

class ErrorController extends Controller
{
   function index($exception)
   {
      $this->exception = $exception;
    //  $this->view_file = 'error/index';
   }
}