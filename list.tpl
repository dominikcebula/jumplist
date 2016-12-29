{if $error}
    <div class="alert error">
        <h3>Task save error</h3>
        {$error}
    </div>
{/if}

<a href="{$request}&addtask=1">
    <img border="0" src="../img/admin/add.gif"/>
    {l s="Add new task to jumplist"}
</a><br/><br/>

<table class="table" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th style="width: 24px;">{l s="ID"}</th>
            <th style="width: 16px;">{l s="Icon"}</th>
            <th style="width: 24px;">{l s="Index"}</th>
            <th style="width: 300px;">{l s="Title"}</th>
            <th style="width: 300px;">{l s="Link"}</th>
            <th style="width: 40px;">{l s="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$jumps item=jump}
            <tr>
                <td>{$jump.id_link}</td>
                <td>{if $jump.icon_url}<img width="16" height="16" border="0" src="{$jump.icon_url}"/>{/if}</td>
                <td>{$jump.idx}</td>
                <td>{$jump.title}</td>
                <td>{$jump.link}</td>
                <td>
                    <a href="{$request}&edittask=1&id={$jump.id_link}">
                        <img title="{l s="Edit"}" alt="{l s="Edit"}" src="../img/admin/edit.gif"/>
                    </a>
                    <a onclick="return confirm('{l s="Delete item?"}');" href="{$request}&deltask=1&id={$jump.id_link}">
                        <img title="{l s="Delete"}" alt="{l s="Delete"}" src="../img/admin/delete.gif"/>
                    </a>
                </td>
            </tr>
        {/foreach}
    </tbody>
</table>
