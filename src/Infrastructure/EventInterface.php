<?php

namespace Infrastructure;

interface EventInterface
{
    public function getData();

    public function __toString();
}