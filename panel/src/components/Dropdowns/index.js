import Dropdown from "./Dropdown.vue";
import DropdownContent from "./DropdownContent.vue";
import DropdownItem from "./DropdownItem.vue";

import LanguagesDropdown from "./LanguagesDropdown.vue";
import OptionsDropdown from "./OptionsDropdown.vue";
import SelectDropdown from "./SelectDropdown.vue";

export default {
	install(app) {
		app.component("k-dropdown", Dropdown);
		app.component("k-dropdown-content", DropdownContent);
		app.component("k-dropdown-item", DropdownItem);

		app.component("k-languages-dropdown", LanguagesDropdown);
		app.component("k-options-dropdown", OptionsDropdown);
		app.component("k-select-dropdown", SelectDropdown);
	}
};
