const form = document.getElementById("formProduksi");
const tableBody = document.getElementById("tableBody");

let dataProduksi = JSON.parse(localStorage.getItem("produksi")) || [];

renderTable();

form.addEventListener("submit", function(e){
    e.preventDefault();
    if(!validate()) return;

    const produksi = {
        komoditas: komoditas.value,
        estimasi: estimasi.value,
        kapasitas: kapasitas.value,
        kualitas: kualitas.value,
        harga: harga.value
    };

    const editIndex = document.getElementById("editIndex").value;

    if(editIndex === ""){
        dataProduksi.push(produksi);
    } else {
        dataProduksi[editIndex] = produksi;
        document.getElementById("editIndex").value = "";
    }

    localStorage.setItem("produksi", JSON.stringify(dataProduksi));
    form.reset();
    renderTable();
});

function renderTable(){
    tableBody.innerHTML = "";
    dataProduksi.forEach((item,index)=>{
        tableBody.innerHTML += `
        <tr>
            <td>${item.komoditas}</td>
            <td>${item.estimasi}</td>
            <td>${item.kapasitas} kg</td>
            <td>${item.kualitas}</td>
            <td>Rp ${item.harga}</td>
            <td>
                <button class="edit" onclick="editData(${index})">Edit</button>
                <button class="delete" onclick="deleteData(${index})">Hapus</button>
            </td>
        </tr>
        `;
    });
}

function editData(index){
    const item = dataProduksi[index];
    komoditas.value = item.komoditas;
    estimasi.value = item.estimasi;
    kapasitas.value = item.kapasitas;
    kualitas.value = item.kualitas;
    harga.value = item.harga;
    document.getElementById("editIndex").value = index;
}

function deleteData(index){
    if(confirm("Hapus data produksi?")){
        dataProduksi.splice(index,1);
        localStorage.setItem("produksi", JSON.stringify(dataProduksi));
        renderTable();
    }
}

function validate(){
    let valid = true;
    document.querySelectorAll("input[required]").forEach(input=>{
        const error = input.nextElementSibling;
        error.textContent = "";
        if(input.value.trim() === ""){
            error.textContent = "Wajib diisi";
            valid = false;
        }
    });
    return valid;
}