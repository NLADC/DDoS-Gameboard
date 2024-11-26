import { Form, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import * as yup from 'yup';

// Define a global standardValidation using yup
const standardValidation = yup.object({
    username: yup.string().required('Username is required'),
    password: yup.string().required('Password is required'),
});

configure({
    generateMessage: (ctx) => {
        const messages = {
            required: `The field ${ctx.field} is required.`,
            email: `The field ${ctx.field} must be a valid email.`,
            min: `The field ${ctx.field} is too short.`,
            // Add other validation messages here
        };

        const message = messages[ctx.rule.name]
            ? messages[ctx.rule.name]
            : `The field ${ctx.field} is invalid.`;

        return message;
    },
    validateOnInput: true, // Validate fields on input

});

// Define the rule globally
defineRule('isRequired', value => {
    if (!value || !value.trim()) {
        return 'This is required';
    }
    return true;
});

export default {
    install(app) {
        app.component('Form', Form);
        app.component('Field', Field);
        app.component('ErrorMessage', ErrorMessage);
    }
};

export { Form, Field, ErrorMessage, standardValidation };
