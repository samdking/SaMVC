<?php

class ErrorController extends Controller
{
   function index($message, $exception)
   {
      $this->message = $message;
      $this->exception = $exception;
    //  $this->view_file = 'error/index';
   }
}