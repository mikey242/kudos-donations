import {useForm, FormProvider} from "react-hook-form"
import {Form} from "./Form"
import {useState} from "@wordpress/element"

const Address = (props) => {
    const form = useForm({
        defaultValues: {
            test: {
                field: null
            }
        }
    });
    
    const { register, watch, handleSubmit } = form;
    const [result, setResult] = useState("");
    const onSubmit = (data) => setResult(JSON.stringify(data));

    const watched = watch("test.field");

    return (
        <FormProvider {...form}>
            <form onSubmit={handleSubmit(onSubmit)}>
                <label>
                    <input
                        {...register("test.field")}
                        type="radio"
                        value="a"
                        name="test.field"
                        required={true}
                    />{" "}
                    A
                </label>
                <label>
                    <input
                        {...register("test.field")}
                        type="radio"
                        value="b"
                        name="test.field"
                        required={true}
                    />{" "}
                    B
                </label>
                <label>
                    <input
                        {...register("test.field")}
                        type="radio"
                        value="c"
                        name="test.field"
                        required={true}
                    />{" "}
                    C
                </label>

                <p>{result}</p>
                <p>Watched value: {watched}</p>
                <input type="submit" />
            </form>
        </FormProvider>
    );
}

export {Address}