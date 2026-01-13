// js/create-recipe.js

function cloneRow(row) {
  const newRow = row.cloneNode(true);
  newRow.querySelectorAll("input").forEach((inp) => {
    inp.value = "";
  });
  row.parentNode.insertBefore(newRow, row.nextSibling);
}

function removeRow(row) {
  const tbody = row.parentNode;
  if (tbody.children.length <= 1) return; // keep at least one row
  row.remove();
}

document.addEventListener("click", (e) => {
  const addBtn = e.target.closest(".js-add-row");
  const removeBtn = e.target.closest(".js-remove-row");

  if (addBtn) {
    const row = addBtn.closest("tr");
    cloneRow(row);
  }

  if (removeBtn) {
    const row = removeBtn.closest("tr");
    removeRow(row);
  }
});
