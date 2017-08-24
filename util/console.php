<?php
function console_log($data) {
    if(is_array($data) || is_object($data))
    {
        echo("
                <script>
                    if(console.debug!=undefined){
                        console.log('PHP: ".json_encode($data)."');
                    }
				</script>
			");
	} else {
        echo("<script>
				if(console.debug!=undefined){	
					console.log('PHP: ".$data."');
				}</script>");
	}
}