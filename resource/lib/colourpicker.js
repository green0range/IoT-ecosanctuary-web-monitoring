/*
A simple colour picker, to impliment;
Make a div where you want the colourpicker, and an entry where the hex code will go.
import the script:
<script src='colourpicker.js'></script>
Create this script:
<script>
	loadColourPicker(document.getElementById('divname'),document.getElementById('inputname'));
</script>
Your done.
*/

// colour credit: http://www.mulinblog.com/a-color-palette-optimized-for-data-visualization/
var colours = ['4d4d4d',
'5da5da',
'faa43a',
'60bd68',
'f17cb0',
'b2912f',
'b276b2',
'decf3f',
'f15854'];

entry = [];
num = 0;

function loadColourPicker(div, ent)
{
	//entry.push(ent);
	// creates canvas object
	var can = document.createElement('canvas');
	can.id = 'colour_canvas'+num;
	can.width = 500;
	can.height = 30;
	can.setAttribute( "onClick", "javascript: getColour(event, "+num+");" );
	// puts in same container as input
	colCan = div.appendChild(can);
	//draws colours in
	cd = colCan.getContext('2d');
	cd.fillStyle = '#000000';
	for (var i=0;i<9;i++)
	{
		cd.fillStyle = '#'+colours[i];
		cd.fillRect(5+(i*5)+(i*50),5,50,20);
	}
	num++;
	entry.push(ent);
}

function getColour(e, id)
{
	// Selects colour
	var rect = document.getElementById('colour_canvas'+id).getBoundingClientRect();
	var x = e.clientX - rect.left;
	var y = e.clientY - rect.top;
	for (var j=0;j<9;j++)
	{
		if((5+(j*5)+(j*50))<x)
		{
			if(x<((5+(j*5)+(j*50))+50))
			{
				if (5<=y<=25)
				{
					entry[id].value = colours[j];
				}
			}
		}
	}
}
