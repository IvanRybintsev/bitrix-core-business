import { FilterToolbar } from 'mail.client.filtertoolbar';
import { Binding } from 'mail.client.binding';
import { BaseEvent, EventEmitter } from "main.core.events";

export class Mailer
{
	#filter;
	#filterToolbar;
	#binding;
	#mailboxId;
	#startDir;
	#selectedDirectory;

	constructor(config ={
		startDir: 'INBOX',
		filterId: '',
		mailboxId: 0,
		syncAvailable: true,
	})
	{
		//delete the loader (the envelope is bouncing)
		let elements = top.document.getElementsByClassName('mail-loader-modifier');
		for (let element of elements)
		{
			element.classList.remove('mail-loader-modifier');
		}

		this.#mailboxId = config['mailboxId'];
		this.#filter = BX.Main.filterManager.getById(config['filterId']);

		BX.Mail.Home.Counters.setShortcut('',config['startDir']);
		this.setFilterDir('');

		const mailCounterWrapper = document.querySelector('[data-role="mail-counter-toolbar"]');

		const filterToolbar = new FilterToolbar({
			startDir: config['startDir'],
			wrapper: mailCounterWrapper,
			filter: this.#filter,
		});

		filterToolbar.build();
		this.#filterToolbar = filterToolbar;

		this.#binding = new Binding(this.#mailboxId);
		Binding.initButtons();

		EventEmitter.subscribe('Grid::updated', (event) => {
			const [grid] = event.getCompatData();
			if(grid !== {} && grid !== undefined && BX.Mail.Home.Grid.getId() === grid.getId())
			{
				Binding.initButtons();
			}
		});

		EventEmitter.subscribe('BX.Main.Filter:apply', (event) => {
			let dir = this.#filter.getFilterFieldsValues()['DIR'];
			BX.Mail.Home.Counters.setDirectory(dir);
		});

		if(!config['syncAvailable'])
		{
			top.BX.UI.InfoHelper.show('limit_contact_center_mail_box_number');
			let lock = false;
			const handler  = () => {
				if(!lock)
				{
					lock = true;
					top.BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", handler);
					top.BX.SidePanel.Instance.close();
				}
			}
			top.BX.addCustomEvent("SidePanel.Slider:onCloseComplete", handler);
		}

	}

	setFilterDir(name)
	{
		if (!!this.#filter && (this.#filter instanceof BX.Main.Filter))
		{
			const FilterApi = this.#filter.getApi();
			FilterApi.setFields({
				'DIR': name,
			});
			FilterApi.apply();
		}
	}

	getFilterToolbar()
	{
		return this.#filterToolbar;
	}
}