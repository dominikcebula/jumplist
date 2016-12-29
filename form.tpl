{if $error}
    <div class="alert error">
        <h3>Task save error</h3>
        {$error}
    </div>
{/if}

<form enctype="multipart/form-data" action="{$request}" method="post">
    <fieldset>
        <legend>
            {l s="Task"}
        </legend>
        <label>{l s="Title"}:</label><div class="margin-form"><input name="title" type="text" value="{$title}"/></div>
        <label>{l s="Link"}:</label><div class="margin-form"><input name="link" "type="text" value="{$link}"/></div>
        <label>{l s="Index"}:</label><div class="margin-form"><input name="idx" "type="text" value="{$idx}"/></div>
        <label>{l s="Icon in ico format"}:</label><div class="margin-form"><input name="file" type="file"/></div>
        <div class="margin-form">Delete icon: <input name="delicon" type="checkbox" value="1"/></div>

        <input type="hidden" name="id" value="{$id}"/>
        <input type="hidden" name="MAX_FILE_SIZE" value="1000" />

        <div class="margin-form">
            <input class="button" type="submit" name="savetask" value="{l s="Save"}"/>
            <input class="button" type="submit" name="canceltask" value="{l s="Cancel"}"/>
        </div>
    </fieldset>
</form>
