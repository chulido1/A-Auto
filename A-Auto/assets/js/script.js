
function openModal() {
  document.getElementById('deptForm').reset();
  document.getElementById('deptId').value = '';
  document.getElementById('modalTitle').innerText = 'Добавить цех';
  document.getElementById('deptModal').style.display = 'flex';
}

function editDepartment(id, name, chief, desc) {
  document.getElementById('deptId').value = id;
  document.getElementById('deptName').value = name;
  document.getElementById('deptChief').value = chief;
  document.getElementById('deptDesc').value = desc;
  document.getElementById('modalTitle').innerText = 'Редактировать цех';
  document.getElementById('deptModal').style.display = 'flex';
}

function closeModal() {
  document.getElementById('deptModal').style.display = 'none';
}

function deleteDepartment(id) {
  if (confirm('Удалить этот цех?')) {
    window.location.href = '../scripts/departments-handler.php?delete=' + id;
  }
}

document.addEventListener("DOMContentLoaded", () => {
  lucide.createIcons();
});


// ——— УЧАСТКИ ———
// ——— УЧАСТКИ ———
let currentDepartmentId = null;

function openSectionsModal(depId, depName){
  currentDepartmentId = depId;
  document.getElementById('sectionsTitle').innerText = `Участки цеха: ${depName}`;
  document.getElementById('sectionsModal').style.display = 'flex';
  loadSections(depId);
}

function closeSectionsModal(){
  document.getElementById('sectionsModal').style.display = 'none';
  document.getElementById('sectionsTableWrap').innerHTML = '';
}

function loadSections(depId){
  fetch(`../scripts/sections-api.php?action=list&department_id=${depId}`)
    .then(r => r.text())
    .then(html => {
      document.getElementById('sectionsTableWrap').innerHTML = html;
      if (window.lucide) lucide.createIcons();

      const form = document.getElementById('sectionForm');
      if (form){
        form.onsubmit = (e) => {
          e.preventDefault();
          const fd = new FormData(form);
          fetch('../scripts/sections-api.php', { method: 'POST', body: fd })
            .then(r => r.text())
            .then(res => {
              if (res.trim() === 'OK') loadSections(currentDepartmentId);
              else alert('Ошибка сохранения участка');
            });
        };
      }
    });
}

function editSection(id, name, chief, desc) {
  document.getElementById('sectionId').value = id;
  document.getElementById('sectionName').value = name || '';
  document.getElementById('sectionChief').value = chief || '';
  document.getElementById('sectionDesc').value = desc || '';
  document.getElementById('sectionName').focus();
}

function deleteSection(id){
  if (!confirm('Удалить участок?')) return;
  fetch(`../scripts/sections-api.php?action=delete&id=${id}`)
    .then(r => r.text())
    .then(res => {
      if (res.trim() === 'OK') loadSections(currentDepartmentId);
      else alert('Ошибка удаления участка');
    });
}


// ==== Employees modal
function openEmpModal(){
  document.getElementById('empTitle').textContent = 'Добавить сотрудника';
  document.getElementById('empForm').reset();
  document.getElementById('empId').value = '';
  document.getElementById('empModal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}
function closeEmpModal(){ document.getElementById('empModal').style.display = 'none'; }

function editEmp(id, fio, pos, cat, prof, grade, exp, phone, email, depName, secName, brigName, hire, status){
  // разобьём ФИО на части для полей
  const [last='', first='', ...rest] = (fio || '').trim().split(' ');
  document.getElementById('empTitle').textContent = 'Редактировать сотрудника';
  document.getElementById('empId').value = id;
  document.getElementById('empLast').value = last;
  document.getElementById('empFirst').value = first;
  document.getElementById('empPatr').value = rest.join(' ');
  document.getElementById('empPosition').value = pos || '';
  document.getElementById('empCategory').value = cat || 'Рабочий';
  document.getElementById('empProfession').value = prof || '';
  document.getElementById('empGrade').value = grade || '';
  document.getElementById('empExp').value = exp || '';
  document.getElementById('empPhone').value = phone || '';
  document.getElementById('empEmail').value = email || '';
  document.getElementById('empHire').value = hire || '';
  document.getElementById('empStatus').value = status || 'работает';

  // селекты по названию (упрощённо)
  const depSel = document.getElementById('empDep');
  const secSel = document.getElementById('empSec');
  const brigSel = document.getElementById('empBrig');
  [...depSel.options].forEach(o => o.selected = (o.text === depName));
  [...secSel.options].forEach(o => o.selected = (o.text.includes(secName)));
  [...brigSel.options].forEach(o => o.selected = (o.text.includes(brigName)));

  document.getElementById('empModal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}

function deleteEmp(id){
  if (!confirm('Удалить сотрудника?')) return;
  const f = document.createElement('form');
  f.method='POST'; f.action='../scripts/employees-handler.php';
  f.innerHTML = '<input name="id" value="'+id+'"><input name="delete" value="1">';
  document.body.appendChild(f); f.submit();
}

// ==== Brigades modal (Ajax)
function openBrigadesModal(){
  document.getElementById('brigadesModal').style.display = 'flex';
  loadBrigades();
}
function closeBrigadesModal(){
  document.getElementById('brigadesModal').style.display = 'none';
  document.getElementById('brigadesWrap').innerHTML = '';
}
function loadBrigades() {
  fetch('../scripts/brigades-api.php?action=list')
    .then(r => r.text())
    .then(html => {
      document.getElementById('brigadesWrap').innerHTML = html;

      if (window.lucide) lucide.createIcons();

      const form = document.getElementById('brigForm');
      if (form) {
        // Перехватываем отправку формы
        form.addEventListener('submit', (e) => {
          e.preventDefault();

          const fd = new FormData(form);
          fetch('../scripts/brigades-api.php', {
            method: 'POST',
            body: fd
          })
          .then(r => r.text())
          .then(res => {
            if (res.trim() === 'OK') {
              loadBrigades(); // обновляем таблицу
            } else {
              alert('Ошибка при сохранении бригады: ' + res);
            }
          })
          .catch(err => alert('Ошибка запроса: ' + err));
        });
      }
    })
    .catch(err => console.error('Ошибка загрузки списка бригад:', err));
}

function editBrig(id, name){
  document.getElementById('brigId').value = id;
  document.getElementById('brigName').value = name || '';
}
function deleteBrig(id){
  if (!confirm('Удалить бригаду?')) return;
  fetch('../scripts/brigades-api.php?action=delete&id='+id)
    .then(r=>r.text()).then(res=>{ if(res.trim()==='OK') loadBrigades(); });
}

document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) lucide.createIcons();

  const addEmpBtn = document.getElementById('btnAddEmp');
  if (addEmpBtn) addEmpBtn.addEventListener('click', openEmpModal);

  const brigBtn = document.getElementById('btnOpenBrigades');
  if (brigBtn) brigBtn.addEventListener('click', openBrigadesModal);
});

// === Управление изделиями ===

// открыть модалку «Добавить»
window.openProductModal = function openProductModal() {
  const f = document.getElementById('productForm');
  f.reset();

  // сброс скрытого id и явный сброс селектов
  document.getElementById('prodId').value = '';
  document.getElementById('prodCat').value = '';
  document.getElementById('prodDep').value = '';
  document.getElementById('prodSec').value = '';

  // статус — выберем первый вариант по умолчанию
  const st = document.getElementById('prodStatus');
  if (st && st.options.length) st.selectedIndex = 0;

  document.getElementById('prodTitle').innerText = 'Добавить изделие';
  document.getElementById('productModal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
};

// закрыть модалку
window.closeProductModal = function closeProductModal() {
  document.getElementById('productModal').style.display = 'none';
};

// редактировать (порядок параметров ДОЛЖЕН совпадать с products.php)
window.editProduct = function editProduct(id, model, serial, cat, dep, sec, start, qty, status, notes) {
  document.getElementById('prodTitle').innerText = 'Редактировать изделие';

  document.getElementById('prodId').value     = id;
  document.getElementById('prodModel').value  = model || '';
  document.getElementById('prodSerial').value = serial || '';

  document.getElementById('prodCat').value = cat || '';
  document.getElementById('prodDep').value = dep || '';
  document.getElementById('prodSec').value = sec || '';

  document.getElementById('prodStart').value   = start || '';
  document.getElementById('prodQty').value     = qty   || '';
  document.getElementById('prodStatus').value  = status || '';
  document.getElementById('prodNotes').value   = notes  || '';

  document.getElementById('productModal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
};

// удалить
window.deleteProduct = function deleteProduct(id) {
  if (!confirm('Удалить это изделие?')) return;
  window.location.href = '../scripts/products-handler.php?delete=' + id;
};

// === Лаборатории ===
function openLabModal() {
  document.getElementById('labForm').reset();
  document.getElementById('labId').value = '';
  document.getElementById('labTitle').innerText = 'Добавить лабораторию';
  document.getElementById('labModal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}

function closeLabModal() {
  document.getElementById('labModal').style.display = 'none';
}

function editLab(id, name, desc) {
  document.getElementById('labId').value = id;
  document.getElementById('labName').value = name || '';
  document.getElementById('labDesc').value = desc || '';
  document.getElementById('labTitle').innerText = 'Редактировать лабораторию';
  document.getElementById('labModal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}

function deleteLab(id) {
  if (!confirm('Удалить лабораторию?')) return;
  window.location.href = '../scripts/labs-handler.php?delete=' + id;
}

// === Оборудование ===
function openEquipmentModal(labId, labName) {
  document.getElementById('equipTitle').innerText = `Оборудование лаборатории: ${labName}`;
  document.getElementById('equipmentModal').style.display = 'flex';

  fetch(`../scripts/equipment-api.php?action=list&lab_id=${labId}`)
    .then(r => r.text())
    .then(html => {
      document.getElementById('equipmentWrap').innerHTML = html;
      if (window.lucide) lucide.createIcons();
    });
}

function closeEquipmentModal() {
  document.getElementById('equipmentModal').style.display = 'none';
  document.getElementById('equipmentWrap').innerHTML = '';
}
