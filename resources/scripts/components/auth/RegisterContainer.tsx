import * as React from 'react';
import { useEffect, useRef, useState } from 'react';
import { Link, Redirect } from 'react-router-dom';
import register from '@/api/auth/register';
import { httpErrorToHuman } from '@/api/http';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import Field from '@/components/elements/Field';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import Reaptcha from 'reaptcha';
import useFlash from '@/plugins/useFlash';

interface Values {
    email: string;
    username: string;
    nameFirst: string;
    nameLast: string;
}

export default () => {
    const ref = useRef<Reaptcha>(null);
    const [token, setToken] = useState('');

    const { clearFlashes, addFlash } = useFlash();
    const { enabled: recaptchaEnabled, siteKey } = useStoreState((state) => state.settings.data!.recaptcha);
    const { enabled: registrationEnabled } = useStoreState((state) => state.settings.data!.registration);

    useEffect(() => {
        clearFlashes();
    }, []);

    // Redirect to login if registration is disabled.
    if (!registrationEnabled) {
        return <Redirect to={'/auth/login'} />;
    }

    const handleSubmission = (
        values: Values,
        { setSubmitting, resetForm }: FormikHelpers<Values>
    ) => {
        clearFlashes();

        // If there is no token in the state yet, request the token and then abort this submit request
        // since it will be re-submitted when the recaptcha data is returned by the component.
        if (recaptchaEnabled && !token) {
            ref.current!.execute().catch((error) => {
                console.error(error);

                setSubmitting(false);
                addFlash({ type: 'error', title: 'Error', message: httpErrorToHuman(error) });
            });

            return;
        }

        register({ ...values, recaptchaData: token })
            .then((response) => {
                resetForm();
                addFlash({
                    type: 'success',
                    title: 'Success',
                    message: response || 'Check your email to set your password and activate your account.',
                });
            })
            .catch((error) => {
                console.error(error);
                addFlash({ type: 'error', title: 'Error', message: httpErrorToHuman(error) });
            })
            .then(() => {
                setToken('');
                if (ref.current) ref.current.reset();

                setSubmitting(false);
            });
    };

    return (
        <Formik
            onSubmit={handleSubmission}
            initialValues={{ email: '', username: '', nameFirst: '', nameLast: '' }}
            validationSchema={object().shape({
                email: string()
                    .email('A valid email address must be provided.')
                    .required('A valid email address must be provided.'),
                username: string()
                    .min(3, 'Username must be at least 3 characters.')
                    .required('A username is required.'),
                nameFirst: string().required('Your first name is required.'),
                nameLast: string().required('Your last name is required.'),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={'Create Account'} css={tw`w-full flex`}>
                    <Field light label={'Email'} name={'email'} type={'email'} />
                    <div css={tw`mt-6`}>
                        <Field light label={'Username'} name={'username'} type={'text'} />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field light label={'First Name'} name={'nameFirst'} type={'text'} />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field light label={'Last Name'} name={'nameLast'} type={'text'} />
                    </div>
                    <div css={tw`mt-6`}>
                        <Button type={'submit'} size={'xlarge'} disabled={isSubmitting} isLoading={isSubmitting}>
                            Register
                        </Button>
                    </div>
                    {recaptchaEnabled && (
                        <Reaptcha
                            ref={ref}
                            size={'invisible'}
                            sitekey={siteKey || '_invalid_key'}
                            onVerify={(response) => {
                                setToken(response);
                                submitForm();
                            }}
                            onExpire={() => {
                                setSubmitting(false);
                                setToken('');
                            }}
                        />
                    )}
                    <div css={tw`mt-6 text-center`}>
                        <Link
                            to={'/auth/login'}
                            css={tw`text-xs text-neutral-500 tracking-wide no-underline uppercase hover:text-neutral-600`}
                        >
                            Already have an account?
                        </Link>
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
};
