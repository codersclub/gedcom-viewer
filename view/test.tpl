

<?smarty $results[0].indiv.name ?><br />
x <?smarty $results[0].family.marriage_date ?><br />
<?smarty $results[0].wife.name ?><br />

<?smarty foreach from=$results[0].children item=item key=key ?>
    
    <?smarty $item.indiv.name ?><br />
    x <?smarty $item.family.marriage_date ?><br />
    <?smarty $item.wife.name ?><br />
<?smarty /foreach ?>

