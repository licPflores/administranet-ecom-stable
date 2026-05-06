<script>
//Setup some private variables
var Dom = YAHOO.util.Dom,
Event = YAHOO.util.Event;

        //The SimpleEditor config
        var myConfig = { height: '150px', width: '570px',
                                        dompath: false,
                                        handleSubmit: true,
                                        focusAtStart: true,
                                        toolbar: {
                                                                titlebar: 'Contenido',
                                                                buttonType: 'advanced',
                                                                buttons: [
                                                                                { group: 'textstyle', label: 'Estilo de Fuente',
                                                                                        buttons: [
                                                                                                { type: 'push', label: 'Negrita', value: 'bold' },
                                                                                                { type: 'push', label: 'Cursiva', value: 'italic' },
                                                                                                { type: 'push', label: 'Subrayado', value: 'underline' },
                                                                                                { type: 'separator' },
                                                                                                { type: 'select', label: 'Arial', value: 'fontname', disabled: true,
                                                                                                        menu: [
                                                                                                                { text: 'Arial', checked: true },
                                                                                                                { text: 'Arial Black' },
                                                                                                                { text: 'Comic Sans MS' },
                                                                                                                { text: 'Courier New' },
                                                                                                                { text: 'Lucida Console' },
                                                                                                                { text: 'Tahoma' },
                                                                                                                { text: 'Times New Roman' },
                                                                                                                { text: 'Trebuchet MS' },
                                                                                                                { text: 'Verdana' }
                                                                                                        ]
                                                                                                },
                                                                                                { type: 'spin', label: '13', value: 'fontsize', range: [ 9, 75 ], disabled: true },
                                                                                                { type: 'separator' },
                                                                                                { type: 'color', label: 'Color de Fuente', value: 'forecolor', disabled: true },
                                                                                                { type: 'color', label: 'Color de Fondo', value: 'backcolor', disabled: true }
                                                                                        ]
                                                                                },//fin grup
                                                                                { type: 'separator' },
                                                                                { group: 'insertitem', label: 'Insertar Item',
                                                                                        buttons: [
                                                                                                { type: 'push', label: 'HTML Link CTRL + SHIFT + L', value: 'createlink', disabled: true },
                                                                                                { type: 'push', label: 'Insert Image', value: 'insertimage' }
                                                                                                        ]
                                                                                }//cierra el nuevo grupo
                                                                                ]//cierro el boton
                                                                }//cierra la barra

                                        }//cierro la creacion de las caracteristicas

            //Now let's load the SimpleEditor..
            //var myEditor = new YAHOO.widget.SimpleEditor('editor', myConfig);

            var myConfigEn = { height: '150px', width: '570px',
                                        dompath: false,
                                        handleSubmit: true,
                                        focusAtStart: true,
                                        toolbar: {
                                                                titlebar: 'Contenido Inglés',
                                                                buttonType: 'advanced',
                                                                buttons: [
                                                                                { group: 'textstyle', label: 'Estilo de Fuente',
                                                                                        buttons: [
                                                                                                { type: 'push', label: 'Negrita', value: 'bold' },
                                                                                                { type: 'push', label: 'Cursiva', value: 'italic' },
                                                                                                { type: 'push', label: 'Subrayado', value: 'underline' },
                                                                                                { type: 'separator' },
                                                                                                { type: 'select', label: 'Arial', value: 'fontname', disabled: true,
                                                                                                        menu: [
                                                                                                                { text: 'Arial', checked: true },
                                                                                                                { text: 'Arial Black' },
                                                                                                                { text: 'Comic Sans MS' },
                                                                                                                { text: 'Courier New' },
                                                                                                                { text: 'Lucida Console' },
                                                                                                                { text: 'Tahoma' },
                                                                                                                { text: 'Times New Roman' },
                                                                                                                { text: 'Trebuchet MS' },
                                                                                                                { text: 'Verdana' }
                                                                                                        ]
                                                                                                },
                                                                                                { type: 'spin', label: '13', value: 'fontsize', range: [ 9, 75 ], disabled: true },
                                                                                                { type: 'separator' },
                                                                                                { type: 'color', label: 'Color de Fuente', value: 'forecolor', disabled: true },
                                                                                                { type: 'color', label: 'Color de Fondo', value: 'backcolor', disabled: true }
                                                                                        ]
                                                                                },//fin grup
                                                                                { type: 'separator' },
                                                                                { group: 'insertitem', label: 'Insertar Item',
                                                                                        buttons: [
                                                                                                { type: 'push', label: 'HTML Link CTRL + SHIFT + L', value: 'createlink', disabled: true },
                                                                                                { type: 'push', label: 'Insert Image', value: 'insertimage' }
                                                                                                        ]
                                                                                }//cierra el nuevo grupo
                                                                                ]//cierro el boton
                                                                }//cierra la barra

                                        }//cierro la creacion de las caracteristicas
           var myEditor = new YAHOO.widget.Editor('txtresumen', myConfig);
            myEditor.render();
           var myEditorEn = new YAHOO.widget.Editor('txtresumen_en',myConfigEn);
            myEditorEn.render();
</script>
