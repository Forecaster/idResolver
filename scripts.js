/*
$(document).ready(function()
{
  $('input[name="all"],input[name="title"]').bind('click', function()
  {
    var status = $(this).is(':checked');
    $('input[type="checkbox"]', $(this).parent('div')).attr('checked', status);
  });
});
*/

function toggleConfigs()
{
  var elements = document.getElementsByTagName('div');

  for (var i=0;i<elements.length;i++)
  {
    //alert("Element class: " + elements[i].className);
    if (elements[i].className == 'configBox overflowing')
    {
      //alert("Found matching element!");
      toggleHidden(elements[i], null);
      //elements[i].style.visibility = 'hidden';
      //elements[i].style.height = 0;
    }
  }
}

function toggle(source)
{
  var aInputs = document.getElementsByTagName('input');
  for (var i=0;i<aInputs.length;i++)
  {
    if (aInputs[i].getAttribute('type') == 'checkbox' && aInputs[i] != source)
    {
      if (aInputs[i].className == source.className || aInputs[i].className == source.className + "Item" || aInputs[i].className == source.className + "Block" )
      {
        aInputs[i].checked = source.checked;
      }
    }
  }
}

function toggleType(source, type)
{
  var aInputs = document.getElementsByTagName('input');
  for (var i=0;i<aInputs.length;i++)
  {
    if (aInputs[i].getAttribute('type') == 'checkbox' && aInputs[i] != source && aInputs[i].className == source.className + type)
    {
      aInputs[i].checked = source.checked;
    }
  }
}

function toggleAll(source)
{
  var aInputs = document.getElementsByTagName('input');
  for (var i=0;i<aInputs.length;i++)
  {
    if (aInputs[i].getAttribute('type') == 'checkbox')
    {
      aInputs[i].checked = source.checked;
    }
  }
}

function toggleHiddenBlock(source, target, height)
{
  var thing = document.getElementById(target);
  
  if (thing.style.visibility != 'hidden')
  {
    toggleHidden(thing, height);
    document.getElementById(target + " togglebutton").innerHTML="+";
  }
  else
  {
    toggleHidden(thing, height);
    document.getElementById(target + " togglebutton").innerHTML="-";
  }
}

function toggleHidden(target, height)
{
  if (target.style.visibility == 'hidden')
  {
    target.style.visibility = 'visible';
    target.style.height = height;
  }
  else
  {
    target.style.visibility = 'hidden';
    target.style.height = 0;
  }
}

function hide(target)
{
  if (target.style.visibility != 'hidden')
  {
    target.style.visibility = 'hidden';
    target.style.height = 0;
  }
}

function hideConflicts(string)
{
  var targets = document.getElementsByClassName('conflictBox');
  
  for (var i = 0; i < targets.length; i++)
  {
    if (targets[i].id.indexOf(string) == 0)
    {
      hide(targets[i]);
    }
  }
}