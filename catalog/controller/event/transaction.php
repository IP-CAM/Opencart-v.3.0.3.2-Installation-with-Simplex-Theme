<?php

class ControllerEventTransaction extends Controller
{
    //
    public function redirect()
    {
        $this->response->redirect("http://simplex.dev.it-lab.md/");
    }

    public function callback()
    {
        var_dump('true');
    }
}