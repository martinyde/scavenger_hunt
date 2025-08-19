// JavaScript to handle adding/removing solutions
export function initForm() {
  const solutionsWrapper = document.getElementById('solutions-wrapper');
  if (solutionsWrapper !== null) {

    const addButton = document.getElementById('add-solution');

    // Setup counter for unique indices
    let index = solutionsWrapper.querySelectorAll('.solution-row').length;

    // Add new solution field
    addButton.addEventListener('click', function() {
      const prototype = solutionsWrapper.dataset.prototype;
      const newForm = prototype.replace(/__name__/g, index);

      const div = document.createElement('div');
      div.classList.add('solution-row');
      div.innerHTML = newForm;

      // Add a remove button
      const removeButton = document.createElement('button');
      removeButton.type = 'button';
      removeButton.classList.add('btn', 'btn-danger', 'btn-sm', 'remove-solution');
      removeButton.textContent = 'Remove';
      div.appendChild(removeButton);

      solutionsWrapper.appendChild(div);
      index++;
    });

  // Remove solution field (using event delegation)
    solutionsWrapper.addEventListener('click', function(e) {
      if (e.target.classList.contains('remove-solution')) {
        e.target.closest('.solution-row').remove();
      }
    });
  }
}