<?php

class ErrorController extends Controller
{
   function page_not_found($exception)
   {
      $this->exception = $exception;
    //  $this->view_file = 'error/index';
   }
}