<!DOCTYPE html>
<html>
  <head>
	<title>Nany Web Editor</title>
	<meta charset="utf-8">
	<!-- Stylesheets -->
	<link href="lib/jquery-ui/jquery-ui.min.css" rel="stylesheet">
	<link href="lib/jstree-themes/default-dark/style.min.css" rel="stylesheet">
	<link href="lib/dropzone.min.css" rel="stylesheet">
	<link href="lib/codemirror/lib/codemirror.css" rel="stylesheet">
	<link href="lib/codemirror/theme/bespin.css" rel="stylesheet">
	<link href="lib/codemirror/addon/fold/foldgutter.css" rel="stylesheet">
	<link href="style.css" rel="stylesheet">
	<!-- UI libraries -->
	<script type="text/javascript" src="lib/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="lib/jquery-ui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="lib/jstree.min.js"></script>
	<script type="text/javascript" src="lib/dropzone.min.js"></script>
	<script type="text/javascript" src="lib/spin.min.js"></script>
	<script type="text/javascript" src="lib/jquery.spin.js"></script>
	<script type="text/javascript" src="lib/codemirror/lib/codemirror.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/edit/matchbrackets.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/edit/trailingspace.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/fold/foldcode.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/fold/foldgutter.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/fold/brace-fold.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/fold/comment-fold.js"></script>
	<script type="text/javascript" src="lib/codemirror/mode/nany/nany.js"></script>
  </head>

  <body>
	<div id="maintitle" class="title"><h1>Nany Web Editor</h1></div>
	<div id="subtitle" class="title"><h1>Discover Nany now !</h1></div>

	<div id="editors">
	  <button id="new-file">+</button>
	  <div id="editor-tabs">
		<ul>
		  <li class="editor-tab"><a href="#tab-1">New</a></li>
		  <!--<li class="editor-tab"><a href="#file2">Simple loop</a></li> -->
		</ul>

		<form style="position: relative;">
		  <select id="sample-list" onchange="document.location = this.options[this.selectedIndex].value;">
			<option value="#" selected="selected" disabled>Samples...</option>
			<!--<option value="samples/helloworld.ny">Hello world</option>
			<option value="samples/unicode.ny">Unicode</option>-->
		  </select>

		  <textarea id="editor"></textarea>
		</form>
	  </div>

	  <script>
		var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
			lineNumbers: true,
			mode: "nany",
			theme : "bespin",
			extraKeys: {"Ctrl-Q": function(cm){ cm.foldCode(cm.getCursor()); }},
			matchBrackets: true,
			showTrailingSpace: true,
			foldGutter: true,
			gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
		});
	  </script>

	</div>

	<!-- UI -->
	<script>
	  $(function() {
		var tabTitle = "New",
			tabContent = $("#editor"),
			tabTemplate = "<li><a href='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close' role='presentation'>Remove Tab</span></li>",
			tabCounter = 2;

		var tabs = $("#editor-tabs").tabs();

		// Modal dialog init: custom buttons and a "close" callback resetting the form inside
		var dialog = $("#dialog").dialog({
			autoOpen: false,
			modal: true,
			buttons: {
				Add: function() {
					addTab();
					$(this).dialog("close");
				},
				Cancel: function() {
					$(this).dialog("close");
				}
			},
			close: function() {
				form[0].reset();
			}
		});

		// AddTab form: calls addTab function on submit and closes the dialog
		var form = dialog.find("form").on("submit", function(event) {
			addTab();
			dialog.dialog("close");
			event.preventDefault();
		});

		// Actual addTab function: adds new tab using the input from the form above
		function addTab() {
			var label = "New (" + tabCounter + ")",
				id = "tab-" + tabCounter,
				li = $(tabTemplate.replace(/#\{href\}/g, "#" + id).replace(/#\{label\}/g, label)),
			tabContentHtml = tabContent.val();

			tabs.find(".ui-tabs-nav").append(li);
			tabs.append("<div id='" + id + "'><p>" + tabContentHtml + "</p></div>");
			tabs.tabs("refresh");
			tabCounter++;
		}

		// + button: just opens the dialog
		$("#new-file").button().on("click", function() {
			//dialog.dialog("open");
			addTab();
		});

		// Close icon: removing the tab on click
		tabs.on("click", "span.ui-icon-close", function() {
			var panelId = $(this).closest("li").remove().attr("aria-controls");
			$("#" + panelId).remove();
			tabs.tabs("refresh");
		});

		// Alt-BACK also closes the current tab
		tabs.on("keyup", function(event) {
			if (event.altKey && event.keyCode === $.ui.keyCode.BACKSPACE) {
				var panelId = tabs.find(".ui-tabs-active").remove().attr("aria-controls");
				$("#" + panelId).remove();
				tabs.tabs("refresh");
			}
		});
	  });

	  <?php
		 $sampleDir = "./samples";
		 $files = scandir($sampleDir);
	  ?>

	  // Fill the sample list using the contents of the "samples/" folder retrieved by PHP
	  $(function() {
		var sampleIndex = 1;
		var entryTemplate = "<option value='samples/#{href}'>#{label}</option>";
		//var entryTemplate = "<li><a href='#{href}'>#{label}</a></li>";
		var fileList = <?php echo '["' . implode('", "', $files) . '"]' ?>;
		$.each(fileList, function(i, item) {
			if (item !== "." && item !== "..") {
				var entry = $(entryTemplate.replace(/#\{href\}/g, item).replace(/#\{label\}/g, item.replace(/.ny/, "")));
				$("#sample-list").append(entry);
			}
		});
		// Register the sample-list as a jQuery drop-down menu
		$("#sample-list").selectmenu();
	  });

	  // When a new sample is selected, update the CodeMirror editor
	  $("#sample-list").on("selectmenuselect", function(event, ui) {
		var selectedIndex = this.selectedIndex;
		var sampleList = $("#sample-list");
		if (selectedIndex > 0) {
			$.ajax({
				url : document.getElementById("sample-list").options[selectedIndex].value,
				dataType: "text",
				success : function (data) {
					editor.getDoc().setValue(data);
				}
			});
		}
	  });
	</script>

  </body>
</html>