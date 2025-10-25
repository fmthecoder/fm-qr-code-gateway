const { useEffect }      = window.wp.element;
const { decodeEntities } = window.wp.htmlEntities;
const __                 = window.wp.i18n.__;

const settings = window.wc.wcSettings.getSetting('fm-qr-code-gateway_data', {});
const label    = decodeEntities(settings.title) || __('FM QR Code Gateway', 'fm-qr-code-gateway');

const Content = (props) => {
	const { eventRegistration, emitResponse } = props;
	const { onPaymentProcessing }             = eventRegistration;
	useEffect(() => {
		const unsubscribe       = onPaymentProcessing(async () => {
			const input         = document.getElementById('fm_qr_transaction_id');
			const transactionId = input ? input.value.trim() : '';

			// Validation
			if (!transactionId) {
				return {
					type: emitResponse.responseTypes.ERROR,
					message: __(
						'Please enter the Transaction ID / UPI Reference',
						'fm-qr-code-gateway'
					),
				};
			}

			return {
				type: emitResponse.responseTypes.SUCCESS,
				meta: {
					paymentMethodData: {
						fm_qr_transaction_id: transactionId,
					},
				},
			};
		});

		return () => {
			unsubscribe();
		};
	}, [
		emitResponse.responseTypes.ERROR,
		emitResponse.responseTypes.SUCCESS,
		onPaymentProcessing,
	]);

	return window.wp.element.createElement(
		'div',
		null,

		// Description
		settings.description &&
			window.wp.element.createElement(
				'p',
				null,
				decodeEntities(settings.description)
			),

		// QR Code
		settings.qr_url &&
			window.wp.element.createElement('img', {
				src: settings.qr_url,
				alt: 'QR Code',
				style: { maxWidth: '200px', margin: '10px 0' },
			}),

		// Transaction ID Field
		window.wp.element.createElement(
			'p',
			null,
			window.wp.element.createElement(
				'label',
				{ htmlFor: 'fm_qr_transaction_id' },
				__('Transaction ID / UPI Reference', 'fm-qr-code-gateway'),
				' ',
				window.wp.element.createElement('span', { className: 'required' }, '*')
			),
			window.wp.element.createElement('br'),
			window.wp.element.createElement('input', {
				type: 'text',
				id: 'fm_qr_transaction_id',
				name: 'fm_qr_transaction_id',
				placeholder: __('Enter Transaction ID', 'fm-qr-code-gateway'),
				style: { width: '80%', padding: '8px', marginTop: '4px' },
			})
		)
	);
};

const Block_Gateway = {
	name: 'fm-qr-code-gateway',
	label: label,
	content: window.wp.element.createElement(Content, null),
	edit: window.wp.element.createElement(Content, null),
	canMakePayment: () => true,
	ariaLabel: label,
	supports: { features: settings.supports },
};

window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway);
