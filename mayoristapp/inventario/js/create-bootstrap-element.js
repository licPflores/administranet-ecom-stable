/** 
creacion de elementos simples de bootstrap 5
--------------------------------------------

---------------------------- testeo ------------------------
Funciones para crear elementos simples

createAlert
createSlickSlide
createDropdown
createRow
createDiv
createButton
createInput
createSelect
createRange
createInputFile
createCard

createDivDropdown
createcollapse
createButtonGroup
createInputGroup
createHtmlContent
createRadio
createTextarea

------------------------- Ejemplos -------------------------

createAlert({
    target: '#content-form',
    typeAler: 'danger', //success, danger, info
    strong: 'Error 324',
    text: 'Texto del error'
});

createSlickSlide({
    target: '#content-form',
    id: 'slide_23',
    images: [[url: 'https://i.imgur.com/HcJzjOJ.jpg'], [url: 'https://i.imgur.com/HcJzjOJ.jpg']],
    alert: 'true', //true - false (en caso de mostrar mensaje si no hay fotos)
    textAlert: 'No hay foto para mostrar'
});

createDropdown({
    target: '#content-form',
    id: 'button_76',
    textButton: 'Menu tienda',
    class: 'x_class',
    menu: '' //[["label1", "value1"], ["label2", "value2"], ["label3", "value3"]]
});

createRow({
    target: '#content-form',
    id: 'row-12',
    class: 'b-3'
    col: '[[id, 6, 12], [id, 4, 12], [id, 2, 12]]', // arma tres columnas (1->id   2->reolucion monitor    3->resolucion celular)
});

createDiv({
    target: '#content-form',
    id: 'div_54',
    class: 'x_class',
    html: 'Contenido html, puede ser html directo',
});

createButton({
    target: '#row-12',
    name: 'name-button',
    type: 'button',
    id: '76',
    class: 'btn-danger',
    onclick: 'funcionX("bla, bla")',
    value: '',
    htmlText: '<i class="bi bi-search"></i> buscar'
});

createInput({
    target: '#row-12',
    type: 'text',
    id: 'ideInput',
    class: '',
    placeholder: 'placeholder del input',
    value: '',
    required: 'true',
    textLabel: 'test de generacion de input'
    extra: 'disabled'
});

createSelect({
    target: '#row-12',
    id: 'idselect',
    class: 'extraCalss',
    values: [["value1", "label1"], ["value2", "label2"], ["value3", "label3"]],
    textLabel: 'test de generacion de select',
    opSelected: 'opcion inicial'
    
});

createRange({
    target: '#row-12',
    id: '54',
    value: '22',
    labelText: 'Rango 1'
});

createInputFile({
    target: '',
    id: '',
    labelText: '',
    onChange: ''
});

createCard({
    target: '',
    id: '',
    content: '' //["header", "slide", "body", "footer"]
});

*/

function createAlert(options = {
        target: '',
        typeAler: '', //success, danger, info
        strong: '',
        text: ''
    }) {

    let resultado  = document.querySelector(options.target);

    const alert = `
        <div class="alert alert-${options.typeAler} alert-dismissible fade show" role="alert">
            <strong>${options.strong}</strong> ${options.text}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    resultado.innerHTML += alert; 
}

function createSlickSlide(options = {
        target: '',
        id: '',
        images: '',
        alert: '', //true - false
        textAlert: ''
    }) {

    let resultado  = document.querySelector(options.target);
    let imagesLength = options.images.length;
    let itemsImageValues = '';

    if(imagesLength == 0 && options.alert == 'true') {
        const itemsImage = `
            <div class="alert alert-danger" role="alert">
                ${options.textAlert}
            </div>
        `;
        itemsImageValues += itemsImage;
    } else {
        for (let i=0;i<imagesLength;i++) {
            const itemsImage = `
                <div class="slide-item">
                    <img src="${options.images[i].url}">
                </div>
            `;
            itemsImageValues += itemsImage;
        }
    }
    
    const slide = `
        <div class="slick-images" id="${options.id}">
            ${itemsImageValues}
        </div>
    `;

    resultado.innerHTML += slide; 
}

function createDropdown(options = {
        target: '',
        id: '',
        textButton: '',
        class: '',
        ulClass: '',
        menu: '' //[["label1", "value1"], ["label2", "value2"], ["label3", "value3"]]
    }) {

    let resultado  = document.querySelector(options.target);
    let menuLength = options.menu.length;
    let menuValues = '';

    for (let i=0;i<menuLength;i++) {
        const dropdownLi = `
            <li>${options.menu[i][0]} ${options.menu[i][1]}</li>
        `;
        menuValues += dropdownLi;
    }
    
    const dropdown = `
        <li class="${options.class} nav-item dropdown" id="${options.id}">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                ${options.textButton}
            </a>
            <ul class="dropdown-menu ${options.ulClass}">
                ${menuValues}
            </ul>
        </li>
    `;

    resultado.innerHTML += dropdown; 
}

function createDivDropdown(options = {
        target: '',
        id: '',
        textButton: '',
        class: '',
        menu: '' //[["label1", "value1"], ["label2", "value2"], ["label3", "value3"]]
    }) {

    let resultado  = document.querySelector(options.target);
    let menuLength = options.menu.length;
    let menuValues = '';

    for (let i=0;i<menuLength;i++) {
        const dropdownLi = `
            <li><a class="dropdown-item" id="${options.menu[i][0]}" onclick="${options.menu[i][2].replace(/["]+/g, "'")}" > ${options.menu[i][1]}</a></li>
        `;
        menuValues += dropdownLi;
    }

    const dropdown = `
        <div class="dropdown ${options.class}" id="${options.id}">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                ${options.textButton}
            </button>
            <ul class="dropdown-menu">
                ${menuValues}
            </ul>
        </div>
    `;

    resultado.innerHTML += dropdown; 
}

function createRow(options = {
        target: '',
        id: '',
        class: '',
        col: '', // [[id, 6, 12], [id, 4, 12], [id, 2, 12]], (1-> id 2->reolucion monitor 3-> resolucion celular) 
    }) {

    let resultado  = document.querySelector(options.target);

    let colLength = options.col.length;
    let colValues = '';

    for (let i=0;i<colLength;i++) {
        const option = `
            <div class="col-md-${options.col[i][1]} col-${options.col[i][2]}" id="${options.col[i][0]}"></div>
        `;
        colValues += option;
    }

    const row = `
        <div class="row ${options.class}" id="${options.id}">
            ${colValues}
        </div>
    `;

    resultado.innerHTML += row; 
};

function createDiv(options = {
        target: '',
        id: '',
        class: '',
        html: '',
    }) {

    let resultado  = document.querySelector(options.target);

    const div = `
        <div class="${options.class}" id="${options.id}">
            ${options.html}
        </div>
    `;

    resultado.innerHTML += div; 
};

function createHtmlContent(options = {
        target: '',
        html: '',
    }) {

    let resultado  = document.querySelector(options.target);

    const html = `${options.html}`;

    resultado.innerHTML += html; 
};

function createButton(options = {
        target: '',
        name: '',
        type: '',
        id: '',
        class: '',
        onclick: '',
        value: '',
        htmlText: ''
    }) {

    let resultado  = document.querySelector(options.target);

    if(options.class==''){options.class = 'btn-primary'}

    const button = `
        <button type="${options.type}" 
                name="${options.name}" 
                id="${options.id}" 
                class="btn ${options.class}"
                onclick="${options.onclick.replace(/["]+/g, "'")}" 
                value="${options.value}">
        ${options.htmlText}</button>
    `;

    resultado.innerHTML += button; 
};

function createButtonGroup(options = {
        target: '',
        id: '',
        class: '',
        arrInputs: '' // [[type, id, class, onclick, value, htmlText], [type, id, class, onclick, value, htmlText]]
    }) {

    let resultado  = document.querySelector(options.target);
    let optionButtonGroup = '';

    for (let i=0;i<options.arrInputs.length;i++) {
        const option = `
            <button type="${options.arrInputs[i][0]}" 
                    id="${options.arrInputs[i][1]}" 
                    class="btn ${options.arrInputs[i][2]}"
                    onclick="${options.arrInputs[i][3].replace(/["]+/g, "'")}"
                    value="${options.arrInputs[i][4]}">${options.arrInputs[i][5]}</button>
        `;
        optionButtonGroup += option;
    }

    const button = `
        <div class="btn-group ${options.class}" role="group" id="${options.id}">
            ${optionButtonGroup}
        </div>
    `;

    resultado.innerHTML += button; 
};

function createInput(options = {
        target: '',
        type: '',
        id: '',
        class: '',
        placeholder: '',
        inputmode: '',
        value: '',
        required: 'false',
        textLabel: '',
        extra: ''
    }) {

    let resultado  = document.querySelector(options.target);
    let inputRequired = '';

    if(options.required=='true'){inputRequired = 'required'}

    const input = `
        <div class="col-sm-12">
            <div class="form-floating mb-3">
                <input type="${options.type}" class="form-control ${options.class}" id="${options.id}" value="${options.value}" placeholder="${options.placeholder}" inputmode="${options.inputmode}" ${inputRequired} ${options.extra}>
                <label id="label-${options.id}" for="${options.id}">${options.textLabel}</label>
            </div>
        </div>
    `;

    resultado.innerHTML += input; 
};

function createValidation(options = {
        target: '',
        type: '', //valid o invalid
        html: ''
    }) {

    let resultado  = document.getElementById(options.target.replace(/[#]+/g, ""));

    if(resultado.classList.contains('is-valid') || resultado.classList.contains('is-invalid')) {
        let divValidation  = document.getElementById(options.target.replace(/[#]+/g, "")+'Feedback');
        //divValidation.innerHTML = '';
        divValidation.innerHTML = options.html;
    } else {

        resultado.className += ' is-'+options.type;

        let divValidation = document.createElement('div');
        divValidation.className = options.type+'-feedback';
        divValidation.setAttribute("id", options.target.replace(/[#]+/g, "")+'Feedback');
        divValidation.innerHTML = options.html;

        resultado.insertAdjacentElement('afterend', divValidation);

    }
};

function createInputGroup(options = {
        target: '',
        type: '',
        id: '',
        class: '',
        placeholder: '',
        inputmode: '',
        value: '',
        required: 'false',
        textLabel: '',
        extra: ''
    }) {

    let resultado  = document.querySelector(options.target);
    let inputRequired = '';

    if(options.required=='true'){inputRequired = 'required'}

    const inputGroup = `
        <div class="col-sm-12">
            <div class="input-group mb-3">
                <span class="input-group-text" id="group-${options.id}">${options.textLabel}</span>
                <input type="${options.type}" class="form-control ${options.class}" value="${options.value}" aria-label="${options.textLabel}" id="${options.id}" placeholder="${options.placeholder}" inputmode="${options.inputmode}" ${inputRequired} ${options.extra}>
            </div>
        </div>
    `;

    resultado.innerHTML += inputGroup; 
};

function createSelect(options = {
        target: '',
        id: '',
        class: '',
        values: '', //[["value1", "label1"], ["value2", "label2"], ["value3", "label3"]]
        textLabel: '',
        opSelected: ''
    }) {

    let resultado  = document.querySelector(options.target);
    let valuesLength = options.values.length;
    let optionValues = '';
    let selected = '';

    if(options.opSelected != null) {
        const option = `
            <option selected>${options.opSelected}</option>
        `;
        optionValues += option;
    }

    for (let i=0;i<valuesLength;i++) {
        if(options.values[i][2]=='Si') { selected = 'selected'; } else { selected = ''; };
        const option = `
            <option value="${options.values[i][0]}" ${selected}>${options.values[i][1]}</option>
        `;
        optionValues += option;
    }

    const select = `
        <div class="col-sm-12">
            <div class="form-floating mb-3">
                <select class="form-select ${options.class}" id="${options.id}">
                    ${optionValues}
                </select>
                <label for="${options.id}" class="floatingSelect">${options.textLabel}</label>
            </div>
        </div>
    `;

    resultado.innerHTML += select; 
};

function createcollapse(options = {
        target: '',
        id: '',
        idButton: '',
        class: '',
        classButton: '',

    }) {

    let resultado  = document.querySelector(options.target);

    const collapse = `
        <div class="col-sm-12">
            <div class="d-grid gap-2">
                <button class="btn btn-primary ${options.classButton}" id="${options.idButton}" type="button" data-bs-toggle="collapse" data-bs-target="#${options.id}" aria-expanded="false" aria-controls="${options.id}">
                </button>
            </div>
            <div class="collapse ${options.class}" id="${options.id}">
            </div>
        </div>
    `;

    resultado.innerHTML += collapse; 
};

function createRange(options = {
        target: '',
        id: '',
        value: '',
        labelText: ''
    }) {

    let resultado  = document.querySelector(options.target);

    const range = `
        <div class="col-sm-12">
            <div class="input-group range mb-3">
                <label for="customRange2" class="input-group-text label-range">${options.labelText}</label>
                <input type="range" class="form-range custom-range" value="${options.value}" min="0" max="1000" id="${options.id}" oninput="showVal(this.value)" onchange="showVal(this.value)">
                <input type="number" class="input-group-text number-range" name="range_${options.id}" value="${options.value}" inputmode="numeric">
            </div>
        </div>
    `;

    resultado.innerHTML += range; 
};

function createInputFile(options = {
        target: '',
        id: '',
        labelText: '',
        onChange: ''
    }) {

    let resultado  = document.querySelector(options.target);
    let optionContent = '';

    if (options.labelText != null) {
        const option = `
            <label class="input-group-text" for="${options.id}">${options.labelText}</label>
        `;
        optionContent += option;
    }

    const file = `
        <div class="col-sm-12">
            <div class="input-group mb-3">
                <input type="file" class="form-control" id="${options.id}" onchange="${options.onChange}">
                ${optionContent}
            </div>
        </div>
    `;

    resultado.innerHTML += file; 
};


function createCard(options = {
        target: '',
        id: '',
        content: '' //["header", "slide", "body", "footer"]
    }) {

    let resultados  = document.querySelector(options.target);
    let contentLength = options.content.length;
    let optionContent = '';

    for (let i=0;i<contentLength;i++) {
        const option = `
            <div class="card-${options.content[i][0]}" id="${options.content[i][1]}"></div>
        `;
        optionContent += option;
    }

    const card = `
        <div class="card" id="${options.id}">
            ${optionContent}
        </div>
    `;

    resultados.innerHTML += card;
};

function createRadio(options = {
        target: '',
        id: '',
        name: '',
        value: '',
        labelText: '',
        checked: ''
    }) {

    let resultados  = document.querySelector(options.target);

    const radio = `
        <div class="col-sm-12">
            <div class="form-check mb-3">
                <input class="form-check-input" type="radio" id="${options.id}" name="${options.name}" value="${options.value}" onchange="showRadioVal(this.value)" ${options.checked}>
                <label class="form-check-label" for="${options.id}">
                    ${options.labelText}
                </label>
            </div>
        </div>
    `;

    resultados.innerHTML += radio;
};

function createCardTitle(options = {
        target: '',
        id: '',
        title: '',
        subTitle: '',
        finalPrice: '',
        image: ''
    }) {

    let resultados  = document.querySelector(options.target);

    const title = `
        <div class="card-title d-inline-flex">
            <div class="image" style="width: 64px;"><img class="img-fluid" src="${options.image}"></div>
            <div class="ms-2">
                <h6 class="mb-0">${options.title}</h6>
                <h6>${options.subTitle}</h6>
                <h6 style="color: #4f4f4f; font-size: 1.25rem;">${options.finalPrice}</h6>
            </div>
        </div>
    `;

    resultados.innerHTML += title;
};

function createSimpleCardTitle(options = {
    target: '',
    id: '',
    title: '',
}) {

let resultados  = document.querySelector(options.target);

const title = `
    <div class="card-title d-inline-flex">
        <div class="ms-2">
            <h6>${options.title}</h6>
        </div>
    </div>
`;

resultados.innerHTML += title;
};

function createTextarea(options = {
        target: '',
        id: '',
        class: '',
        placeholder: '',
        rows: '',
        textLabel: '',
        text: ''
    }) {

    let resultado  = document.querySelector(options.target);

    const textarea = `
        <div class="col-sm-12">
            <div class="form-floating  mb-3">
                <textarea class="form-control ${options.class}" id="${options.id}"  placeholder="${options.placeholder}" rows="${options.rows}">${options.text}</textarea>
                <label for="${options.id}" >${options.textLabel}</label>
            </div>
        </div>
    `;

    resultado.innerHTML += textarea; 
};