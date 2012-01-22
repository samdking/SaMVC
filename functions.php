<?php

function public_method($obj, $method)
{
   return is_callable(array($obj, $method));
}