<?php
  if (!isset($step))
    $step = 'mode';

  step($step);
?>




    <div class="row">
        <div class="col-md-12">
            <form action='#fromMode' method=post>
                <input type=hidden name=step value='upload' />
                <div class="btn-group">
                    <button type="submit" class="btn btn-success btn-lg">ID Resolver Mode</button>
                    <button type="submit" class="btn btn-success btn-lg" disabled>Biome Resolver Mode (soon)</button>
                    <button type="submit" class="btn btn-success btn-lg" disabled>Settings Mode (soon)</button>
                </div>
            </form>
        </div>
    </div>